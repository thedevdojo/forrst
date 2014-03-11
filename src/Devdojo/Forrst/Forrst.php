<?php
/**
 * PHP wrapper for Forrst.
 * 
 * @author   DevDojo compliments of Martin Bean <martin@martinbean.co.uk> for original wrapper
 * @license  MIT License
 * @version  1.0
 */

namespace Devdojo\Forrst;

/**
 * The core Forrst API PHP wrapper class.
 */
class Forrst
{
    /**
     * Default options for cURL.
     *
     * @var array
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'forrst-php-api-client'
    );
    
    /**
     * Forrst API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'http://forrst.com/api/v2';
    
    /**
     * Forrst API access token.
     *
     * @var string
     */
    protected $accessToken;
    
    
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {}
    
    /**
     * Set base URL.
     *
     * @param  string $url
     * @return void
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }
    
    /**
     * Set access token.
     *
     * @param  string $token
     * @return void
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }
    
    /**
     * Makes an HTTP request.
     * This method can be overriden by subclasses if developers want to use something other than cURL.
     *
     * @param  string $url
     * @param  array  $params
     * @param  string $method
     * @return string
     * @throws ForrstApiException
     */
    protected function makeRequest($url, $params = array(), $method = 'GET')
    {
        $ch = curl_init();
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $this->baseUrl . $url;
        switch (strtolower($method)) {
            case 'get':
                $opts[CURLOPT_URL].= '?'.http_build_query($params, null, '&');
            break;
            case 'post':
                $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
                $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        }
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $result = json_decode($result);
        if ($result === false) {
            $e = new Forrst_Exception(array(
                'type' => 'CurlExcpetion',
                'code' => curl_errno(),
                'message' => curl_error()
            ));
            curl_close($ch);
            throw $e;
        }
        if (is_object($result) && $result->stat == 'fail') {
            $e = new Forrst_Exception(array(
                'type' => 'ForrstApiException',
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'message' => $result->resp->error,
                'url' => $opts[CURLOPT_URL]
            ));
            curl_close($ch);
            throw $e;
        }
        curl_close($ch);
        return $result;
    }
    
    /**
     * Returns stats about your API usage.
     *
     * @return object
     */
    public function getStats()
    {
        return $this->makeRequest('/stats');
    }
    
    /**
     * User authentication.
     * Provide an email/username and password and get an access token back.
     *
     * @param  string $email_or_username
     * @param  string $password
     * @return string
     */
    public function authenticateUser($email_or_username, $password)
    {
        $params = array(
            'email_or_username' => $email_or_username,
            'password' => $password
        );
        $response = $this->makeRequest('/users/auth', $params, 'POST');
        $this->setAccessToken($response->resp->token);
        return $response->resp->token;
    }
    
    /**
     * Returns user information.
     *
     * @param  integer $id
     * @param  string  $username
     * @return object
     */
    public function getUsersInfo($id, $username)
    {
        $params = array(
            'id' => $id,
            'username' => $username
        );
        return $this->makeRequest('/users/info', $params);
    }
    
    /**
     * Returns a user's posts.
     *
     * @param  integer $id
     * @param  string  $username
     * @param  string  $type (one of code, snap, link, question)
     * @param  integer $limit
     * @param  integer $after
     * @return object
     */
    public function getUserPosts($id = null, $username = null, $type = null, $limit = 10, $after = null)
    {
        $params = array(
            'id' => $id,
            'username' => $username,
            'type' => $type,
            'limit' => $limit,
            'after' => $after
        );
        return $this->makeRequest('/user/posts', $params);
    }
    
    /**
     * Return data about a single post.
     * For questions, content is the question.
     * For code, content contains the code snippet.
     * For code, snaps, and links, description is the post description; it is not used for questions.
     *
     * @param  integer $id
     * @param  string  $tiny_id
     * @return object
     */
    public function showPost($id, $tiny_id)
    {
        $params = array(
            'id' => $id,
            'tiny_id' => $tiny_id
        );
        return $this->makeRequest('/posts/show', $params);
    }
    
    /**
     * Returns a list of posts of a given type.
     *
     * @param  string  $post_type (one of code, snap, link, question)
     * @param  string  $sort (one of recent, popular, best (staff picks))
     * @param  integer $page
     * @return object
     */
    public function listPosts($post_type, $sort = 'recent', $page = 1)
    {
        $params = array(
            'post_type' => $post_type,
            'sort' => $sort,
            'page' => $page
        );
        return $this->makeRequest('/posts/list', $params);
    }
    
    /**
     * Returns a post's comments.
     *
     * @param  integer $id
     * @param  string  $tiny_id
     * @return object
     */
    public function getPostComments($id, $tiny_id)
    {
        $params = array(
            'id' => $id,
            'tiny_id' => $tiny_id,
            'access_token' => $this->accessToken
        );
        return $this->makeRequest('/post/comments', $params);
    }
    
    /**
     * Returns user notifications.
     *
     * @param  string $grouped
     * @return object
     */
    public function getUserNotifications($grouped = null)
    {
        $params = array(
            'grouped' => $grouped,
            'access_token' => $this->accessToken
        );
        return $this->makeRequest('/notifications', $params);
    }
}