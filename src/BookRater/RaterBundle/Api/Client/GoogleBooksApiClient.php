<?php

namespace BookRater\RaterBundle\Api\Client;

use BookRater\RaterBundle\Entity\Author;
use BookRater\RaterBundle\Entity\Book;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Google_Service_Books;
use Google_Service_Books_Volumes;
use Google_Service_Books_VolumeVolumeInfo;

class GoogleBooksApiClient
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Google_Service_Books
     */
    private $googleBooksClient;

    /**
     * GoogleBooksApiClient constructor.
     * @param EntityManagerInterface $em
     * @param Google_Service_Books $googleBooksClient
     */
    public function __construct(EntityManagerInterface $em, Google_Service_Books $googleBooksClient)
    {
        $this->em = $em;
        $this->googleBooksClient = $googleBooksClient;
    }

    public function updateGoogleBooksRating(Book $book)
    {
        $googleBooksId = $book->getGoogleBooksId();

        if ($googleBooksId) {
            $volume = $this->googleBooksClient->volumes->get($googleBooksId);
            $book->setGoogleBooksRating($volume->getVolumeInfo()->getAverageRating());
        } else {
            $this->updateGoogleBooksInfo($book);
        }
    }

    /**
     * @param Book $book
     */
    public function updateGoogleBooksInfo(Book $book): void
    {
        if (!$book->getGoogleBooksId()) {
            /** @var Google_Service_Books_Volumes $volumes */
            $volumes = $this->googleBooksClient->volumes->listVolumes('isbn:' . $book->getIsbn());
            if ($volumes->getItems()) {
                /** @var \Google_Service_Books_Volume $bestMatch */
                $bestMatch = $volumes->getItems()[0];
                $volumeInfo = $bestMatch->getVolumeInfo();

                $book->setGoogleBooksId($bestMatch->getId());
                $this->updateGoogleUrls($book, $bestMatch);
                $this->updateBookFromVolumeInfo($book, $volumeInfo);
            }
        }
    }

    /**
     * @param Book $book
     * @param Google_Service_Books_VolumeVolumeInfo $volumeInfo
     */
    private function updateBookFromVolumeInfo(Book $book, Google_Service_Books_VolumeVolumeInfo $volumeInfo): void
    {
        $this->setISBNNumbers($book, $volumeInfo);
        $book->setPublishDate(new DateTime($volumeInfo->publishedDate));
        if ($book->getAuthors()->isEmpty()) {
            $this->setAuthors($book, $volumeInfo);
        }
        if (!$book->getDescription()) {
            $book->setDescription($volumeInfo->getDescription());
        }
        if (!$book->getPublisher()) {
            $book->setPublisher($volumeInfo->getPublisher());
        }
        $book->setGoogleBooksRating($volumeInfo->getAverageRating());
    }

    /**
     * @param Book $book
     * @param Google_Service_Books_VolumeVolumeInfo $volumeInfo
     */
    private function setISBNNumbers(Book $book, Google_Service_Books_VolumeVolumeInfo $volumeInfo): void
    {
        $identifiers = [];
        foreach ($volumeInfo->getIndustryIdentifiers() as $identifier) {
            $identifiers[$identifier->type] = $identifier->identifier;
        }
        if (isset($identifiers['ISBN_10'])) {
            $book->setIsbn($identifiers['ISBN_10']);
        }
        if (!$book->getIsbn13() && isset($identifiers['ISBN_13'])) {
            $book->setIsbn13($identifiers['ISBN_13']);
        }
    }

    /**
     * @param Book $book
     * @param Google_Service_Books_VolumeVolumeInfo $volumeInfo
     */
    private function setAuthors(Book $book, Google_Service_Books_VolumeVolumeInfo $volumeInfo): void
    {
        $authors = $volumeInfo->getAuthors();
        if (!empty($authors)) {
            $authorRepo = $this->em->getRepository('BookRaterRaterBundle:Author');
            foreach ($authors as $authorName) {
                $authorNameArray = $this->formatName($authorName);
                $lastName = $authorNameArray[count($authorNameArray) - 1];
                $firstName = $authorNameArray[0];
                try {
                    $savedAuthor = $authorRepo
                        ->findOneByName($lastName, $firstName);
                    if (!$savedAuthor) {
                        $savedAuthor = new Author();
                        $savedAuthor->setLastName($lastName);
                        $savedAuthor->setFirstName($firstName);
                        if ($this->hasInitials($authorNameArray)) {
                            $savedAuthor->setInitial($this->getInitials($authorNameArray));
                        }
                        $this->em->persist($savedAuthor);
                    }
                    $book->addAuthor($savedAuthor);
                } catch (NonUniqueResultException $e) {
                    // this actually cant happen as there's a unique constraint on first + last name
                }
            }
        }
    }

    /**
     * @param $authorName
     * @return array
     */
    private function formatName(string $authorName): array
    {
        $nameWithoutTitles = strpos($authorName, ',') ? strstr($authorName, ',', true) : $authorName;
        $nameWithoutStupidCharacters = preg_replace("/[^a-zA-Z\- ]/", '', $nameWithoutTitles);
        return explode(' ', $nameWithoutStupidCharacters);
    }

    /**
     * @param array $authorNameArray
     * @return string
     */
    private function getInitials(array $authorNameArray): string
    {
        $middleNames = array_slice($authorNameArray, 1, count($authorNameArray) - 2);
        $initials = '';
        foreach ($middleNames as $middleName) {
            $initials .= $middleName[0] . " ";
        }
        return trim($initials);
    }

    /**
     * @param $authorNameArray
     * @return bool
     */
    private function hasInitials($authorNameArray): bool
    {
        return count($authorNameArray) > 2;
    }

    /**
     * @param Book $book
     * @param $bestMatch
     */
    private function updateGoogleUrls(Book $book, $bestMatch): void
    {
        if (!$book->getGoogleBooksUrl()) {
            $book->setGoogleBooksUrl('http://books.google.co.uk/books?id=' . $bestMatch->getId());
            $book->setGoogleBooksReviewsUrl($book->getGoogleBooksUrl() . '&sitesec=reviews');
        }
    }

}
