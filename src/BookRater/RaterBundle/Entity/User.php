<?php

namespace BookRater\RaterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *   "self",
 *   href=@Hateoas\Route(
 *     "api_users_show",
 *     parameters = { "identifier" = "expr(object.getId())" }
 *   ),
 *   exclusion=@Hateoas\Exclusion(groups={"admin"})
 * )
 * @Hateoas\Relation(
 *   "reviews",
 *   href=@Hateoas\Route(
 *     "api_users_reviews_list",
 *     parameters = { "identifier" = "expr(object.getUsername())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "messages",
 *   href=@Hateoas\Route(
 *     "api_users_messages_list",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion=@Hateoas\Exclusion(groups={"admin"})
 * )
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="BookRater\RaterBundle\Repository\UserRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("user")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"admin"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @SWG\Property(description="The unique identifier of the user.")
     */
    protected $id;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user", cascade={"remove"})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"admin"})
     * @Serializer\XmlList(entry="review")
     *
     * @SWG\Property(description="A list of all reviews created by the user.")
     */
    private $reviews;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="user", cascade={"remove"})
     *
     * @Serializer\Expose
     * @Serializer\Groups({"admin"})
     * @Serializer\XmlList(entry="message")
     *
     * @SWG\Property(description="A list of all messages created by he user.")
     */
    private $messages;

    /**
     * @Serializer\SerializedName("_links")
     * @Serializer\Expose
     * @Serializer\Groups({"books", "authors", "reviews", "messages", "admin"})
     *
     * @SWG\Property(
     *   type="object",
     *   description="A series of resource urls conforming to application/hal+json standards",
     *   @SWG\Property(type="string", property="self", description="A relative url to this resource.",),
     *   @SWG\Property(
     *     type="string",
     *     property="reviews",
     *     description="A relative url to the reviews associated with this resource.",
     *   ),
     *   @SWG\Property(
     *     type="string",
     *     property="messages",
     *     description="A relative url to the messages associated with this resource.",
     *   ),
     * )
     */
    // This is a fake property and will be overridden dynamically during serialisation - here for swagger's benefit
    private $links;

    public function __construct()
    {
        parent::__construct();

        $this->reviews = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * Add review
     *
     * @param Review $review
     *
     * @return User
     */
    public function addReview(Review $review) : User
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     *
     * @return User
     */
    public function removeReview(Review $review) : User
    {
        $this->reviews->removeElement($review);

        return $this;
    }

    /**
     * Get reviews
     *
     * @return Collection|Review[]
     */
    public function getReviews() : Collection
    {
        return $this->reviews;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages() : Collection
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     *
     * @return User
     */
    public function addMessage(Message $message) : User
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     *
     * @param Message $message
     *
     * @return User
     */
    public function removeMessage(Message $message) : User
    {
        $this->messages->removeElement($message);

        return $this;
    }

    /**
     * @return string|null
     */
    public function __toString() : ?string
    {
        return $this->username;
    }

}
