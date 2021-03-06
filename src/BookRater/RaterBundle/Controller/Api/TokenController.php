<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Api\ApiProblem;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokenController extends BaseApiController
{

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws JWTEncodeFailureException
     *
     * @Rest\Post("/tokens")
     *
     * @SWG\Post(
     *   tags={"Tokens"},
     *   summary="Create a new Auth Token",
     *   description="Creates a JWT Bearer token to be used for additional api requests.
                      <strong>Requires Basic auth:</strong>
                      'Basic' followed by base 64 encoded 'username:password' e.g. 'Basic dXNlcjpwYXNzd29yZA=='",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="201",
     *     description="A JWT Bearer token to be used for authentication.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="token", type="string"),
     *       example={
     *         "token":"eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZXhwIjoxNTIxOTE2MjExLCJpYXQiOjE1MjE5MTI2MTF9.TswXDTsKiR0xWVQcWpRLdz-UPsXjld9XklDRhpsVzwd_bz_MAvaBT8-1mwpuXiajU4lA5CLt_6I62yCHvslkSZU3goctgVVFwHllkk8f6bUBe1lFajArdkiuxQJ2TDAC9oaXNyxnFKCV8pwcOjSy9lOVxBN3nEP3O1Hij0fNA3AW2a4qGAgqpr5LiIOC9tWcZvag_iiokNcKn1230QdxKIZIaYnElCBhXWgRvxajKjiFj7IRIGub2ZsNkEz9fAlGc6Bbr5egXACgphUMwcHhEx2GYo2NrbY7t7DQJhtS5CULliD5wasXL23VgZwgosBf_DiY6MevvDS2tFJTOJUnK_LvCs2xBktBNxXddkAYVk3HhQ8TfMplLl6vq0y8unYSv_HlPKojg8ES8k2pq7_F6luvC5Tj_ChPJ3JuVd7nx7pbAMpRT6K6nla2Ck7W7IxCD-eB4RkaZIDUXaomSVTY_Q_MzFh1kqrhV4mBzLioTdgNoR3kdWQt-Q2XksEIg5ap9CaLZlLIBtpcZKNbNpLoy85QLwHp3WqCA0khJBhN53oBf8gDWnkFOFzIK7RqArpehzMcWV4KBzIFfH_dUgJcHPNSkqyq_1QKiBc1xtMhps4I7ygV3bhJ0CB0Tao103ODLc7ys8Jzvi9kejJ9tDXgFzK_1viBeg6vOYg-BMFpuSc"
     *       },
     *     ),
     *   ),
     *   @SWG\Response(
     *     response=404,
     *     description="A 'Not Found' error response, if no Authorization header is provided or the user does not exist.",
     *     @Model(type=ApiProblem::class),
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the password provided is not correct.",
     *     @Model(type=ApiProblem::class),
     *   ),
     * )
     */
    public function newTokenAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository('BookRaterRaterBundle:User')
            ->findOneBy(['username' => $request->getUser()]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->getPassword());

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);

        return new JsonResponse(['token' => $token], Response::HTTP_CREATED);
    }

    protected function getGroups()
    {
        return [];
    }

}
