<?php
namespace Pz\Controller;


use Pz\Form\Handler\RegisterHandler;
use Pz\Orm\Customer;
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

class FacebookLogin extends Controller
{
    const ID = '358957728012867';
    const SECRET = '201dd0de7706e50efff47323d4628e8b';

    /**
     * @route("/facebook/verify", name="verifyFacebook")
     * @return Response
     */
	public function verifyFacebook() {
        $request = Request::createFromGlobals();

        $fb = new Facebook(array(
            'app_id' => static::ID,
            'app_secret' => static::SECRET,
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
            $tokenMetadata->validateAppId(static::ID);
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
            $app['session']->set('fb_access_token', (string) $accessToken);
            try {
                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/me?fields=id,name,email', $app['session']->get('fb_access_token'));
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $fbUser = $response->getGraphUser();
//				var_dump($fbUser->getId(), $fbUser->getEmail(), $fbUser->getName());exit;
            $customer = Customer::data($pdo, array(
                'whereSql' => 'm.title = ? AND m.status = 1',
                'params' => array($userInfo->email),
                'oneOrNull' => 1,
            ));

            $redirectUrl = '\member\dashboard';
            if (!$customer) {
                $customer = new Customer($pdo);
                $customer->setTitle($userInfo->email);
                $customer->setFirstname($userInfo->givenName);
                $customer->setLastname($userInfo->familyName);
                $customer->setSource(RegisterHandler::GOOGLE);
                $customer->setSourceId($userInfo->id);
                $customer->setIsActivated(1);
                $customer->save();
                $redirectUrl = '\member\password';
            }


            $tokenStorage = $this->container->get('security.token_storage');
            $token = new UsernamePasswordToken($customer, $customer->getPassword(), "public", $customer->getRoles());
            $tokenStorage->setToken($token);
            $this->get('session')->set('_security_member', serialize($token));
            return new RedirectResponse($redirectUrl);
        }
	}
}