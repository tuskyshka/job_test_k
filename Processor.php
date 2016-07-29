<?php

/**
 * Created by PhpStorm.
 * User: vk
 */
class Processor
{

    public
        $url,
        $timeout,

        $error,
        $errorCode;

    private
        $pageContent;

    public function __construct($url='')
    {

        if ( ! empty($url) )
            $this->url = $url;

    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @param integer $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = (int) $errorCode;
    }

    protected function getPageContent()
    {
        if ( $this->pageContent === null ) {

            try {

                $ch = curl_init();

                curl_setopt_array($ch, [
                    CURLOPT_URL => $this->getUrl(),
                    
                    CURLOPT_HEADER => false,
                    CURLOPT_CONNECTTIMEOUT => $this->timeout,
                    CURLOPT_TIMEOUT => $this->timeout,
                    CURLINFO_HEADER_OUT => true,

                    // Redirects
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,

                    // Fake client
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0',
                    CURLOPT_REFERER => 'https://www.google.com/',

                    // Also allow cookies
                    CURLOPT_COOKIEFILE => __DIR__ . '/cookies/' . md5($this->getUrl()) . '.txt',
                    CURLOPT_COOKIEJAR => __DIR__ . '/cookies/' . md5($this->getUrl()) . '.txt',
                ]);

                $result = curl_exec($ch);
                $this->errorCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($this->errorCode === 200)
                    $this->pageContent = $result;
                else {
                    $this->pageContent = null;
                    $this->error = curl_error($ch);
                }

                curl_close($ch);
            } catch (Exception $e) {
                $this->errorCode = 500;
                $this->error = $e;
            }

        }

        return $this->pageContent;
    }

    public function process($from, $to)
    {
        return $this->replace($from, $to);
    }

    public function processWithArray($array = [])
    {

        if ( count($array) === 0 )
            return null;

        // Unpredictable result. Associative array keys are int or utf8 string, but it's not good idea to have keys like 'красно солнышко'
        // Anyway, you can use it at own risk.
        //
        // Better way is [ 0 => [ 'what', 'to', 'заменить' ], 1 => [ 'Ну вот', 'уникальный', 'текст готов' ] ]


        if ( count($array) === 2 && is_array($array[0]) )
            $this->pageContent = $this->replace($array[0], $array[1]);
        else
            $this->pageContent = $this->replace(array_keys($array), array_values($array));

        return $this->getPageContent();

    }

    private function replace($from, $to)
    {

        do {

            $pageContent = $this->getPageContent();
            $this->pageContent = str_ireplace($from, $to, $pageContent);

        } while ( $pageContent != $this->getPageContent() );

        return $this->getPageContent();

    }
}
