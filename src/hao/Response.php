<?php


namespace Hao;

/**
 * 响应输出基础类
 * @package think
 */
class Response
{
    /**
     * 原始数据
     * @var mixed
     */
    protected $data;

    /**
     * 状态码
     * @var integer
     */
    protected $code = 200;

    /**
     * 输出内容
     * @var string
     */
    protected $content = null;

    public function __construct($data){
        $this->data = $data;
    }

    /**
     * 发送数据到客户端
     * @access public
     * @return void
     * @throws \InvalidArgumentException
     */
    public function send(): void
    {
        // 处理输出数据
        $data = $this->getContent();
        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name . (!is_null($val) ? ':' . $val : ''));
            }
        }

        $this->sendData($data);

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    /**
     * 获取输出数据
     * @access public
     * @return string
     */
    public function getContent(): string
    {
        if (null == $this->content) {
            $content = $this->output($this->data);
            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                    $content,
                    '__toString',
                ])
            ) {
                throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
            }

            $this->content = (string) $content;
        }

        return $this->content;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }

    /**
     * 输出数据
     * @access protected
     * @param string $data 要处理的数据
     * @return void
     */
    protected function sendData(string $data): void
    {
        echo $data;
    }
}