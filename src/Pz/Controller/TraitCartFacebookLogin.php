<?php
namespace Pz\Controller;


use Pz\Orm\Customer;
use Pz\Service\CartService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Facebook\Facebook;

trait TraitCartFacebookLogin
{
    /**
     * @route("/facebook/verify")
     * @return Response
     */
	public function verifyFacebook() {
        $request = Request::createFromGlobals();

        $fb = new Facebook(array(
            'app_id' => getenv('FACEBOOK_ID'),
            'app_secret' => getenv('FACEBOOK_SECRET'),
            'default_graph_version' => 'v2.12',
        ));
        $helper = $fb->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        if (!isset($accessToken)) {
            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email', 'public_profile']; // Optional permissions
            $loginUrl = $helper->getLoginUrl($request->getScheme() . '://' . $request->getHost() . '/facebook/verify', $permissions);
            return new RedirectResponse($loginUrl);
        } else {
            // Logged in
//				echo '<h3>Access Token</h3>';
//				var_dump($accessToken->getValue());
            // The OAuth 2.0 client handler helps us manage access tokens
            $oAuth2Client = $fb->getOAuth2Client();
            // Get the access token metadata from /debug_token
            $tokenMetadata = $oAuth2Client->debugToken($accessToken);
//				echo '<h3>Metadata</h3>';
//				var_dump($tokenMetadata);
            // Validation (these will throw FacebookSDKException's when they fail)
            $tokenMetadata->validateAppId(getenv('FACEBOOK_ID'));
            // If you know the user ID this access token belongs to, you can validate it here
            // $tokenMetadata->validateUserId('123');
            $tokenMetadata->validateExpiration();
            if (! $accessToken->isLongLived()) {
                // Exchanges a short-lived access token for a long-lived one
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>";
                    exit;
                }
                echo '<h3>Long-lived</h3>';
                var_dump($accessToken->getValue());
            }
//				$_SESSION['fb_access_token'] = (string) $accessToken;
            $this->get('session')->set('fb_access_token', (string) $accessToken);
            try {
                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/me?fields=id,name,email', $this->get('session')->get('fb_access_token'));
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $fbUser = $response->getGraphUser();

            $names = explode(' ', $fbUser->getName());
            $firstName = $names[0];
            if (count($names) > 1) {
                $lastName = join(' ' , array_slice($names, 1));
            } else {
                $lastName = '';
            }

            $connection = $this->container->get('doctrine.dbal.default_connection');
            /** @var \PDO $pdo */
            $pdo = $connection->getWrappedConnection();

            $customer = Customer::data($pdo, array(
                'whereSql' => 'm.title = ? AND m.status = 1',
                'params' => array($fbUser->getEmail()),
                'oneOrNull' => 1,
            ));

            $redirectUrl = '/member/dashboard';
            if (!$customer) {
                $customer = new Customer($pdo);
                $customer->setTitle($fbUser->getEmail());
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setSource(CartService::CUSTOMER_FACEBOOK);
                $customer->setSourceId($fbUser->getId());
                $customer->setIsActivated(1);
                $customer->save();
                $redirectUrl = '/member/password?returnUrl=' . urlencode('/cart');
            } else {
                $cart = new CartService($this->container);
                $orderContainer = $cart->getOrderContainer();
                if (count($orderContainer->getPendingItems())) {
                    $redirectUrl = '/member/after_login';
                }
            }


            $tokenStorage = $this->container->get('security.token_storage');
            $token = new UsernamePasswordToken($customer, $customer->getPassword(), "public", $customer->getRoles());
            $tokenStorage->setToken($token);
            $this->get('session')->set('_security_member', serialize($token));
            return new RedirectResponse($redirectUrl);
        }
	}
}