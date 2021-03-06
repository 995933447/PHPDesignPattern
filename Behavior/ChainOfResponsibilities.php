<?php 
/**
 * 创建处理器抽象类 Handler 。
 */
abstract class Handler
{
    /**
     * @var Handler|null
     * 定义继承处理器
     */
    private $successor = null;

    /**
     * 输入集成处理器对象。
     */
    public function __construct(Handler $handler = null)
    {
        $this->successor = $handler;
    }

    /**
     * 通过使用模板方法模式这种方法可以确保每个子类不会忽略调用继
     * 承。
     *
     * @param RequestInterface $request
     * 定义处理请求方法。
     * 
     * @return string|null
     */
    final public function handle(RequestInterface $request)
    {
        $processed = $this->processing($request);

        if ($processed === null) {
            // 请求尚未被目前的处理器处理 => 传递到下一个处理器。
            if ($this->successor !== null) {
                $processed = $this->successor->handle($request);
            }
        }

        return $processed;
    }

    /**
     * 声明处理方法。
     */
    abstract protected function processing(RequestInterface $request);
}

/**
 * 创建 http 缓存处理类。
 */
class HttpInMemoryCacheHandler extends Handler
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     * 传入数据数组参数。
     * @param Handler|null $successor
     * 传入处理器类对象 $successor 。
     */
    public function __construct(array $data, Handler $successor = null)
    {
        parent::__construct($successor);

        $this->data = $data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param RequestInterface $request
     * 传入请求类对象参数 $request 。
     * @return string|null
     * 
     * 返回缓存中对应路径存储的数据。
     */
    protected function processing(RequestInterface $request)
    {
        $key = sprintf(
            '%s?%s',
            $request->getUri()->getPath(),
            $request->getUri()->getQuery()
        );

        if ($request->getMethod() == 'GET' && isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

}

class SlowDatabaseHandler extends Handler
{
    /**
     * @param RequestInterface $request
     * 传入请求类对象 $request 。
     * 
     * @return string|null
     * 定义处理方法，下面应该是个数据库查询动作，但是简单化模拟，直接返回一个 'Hello World' 字符串作查询结果。
     */
    protected function processing(RequestInterface $request)
    {
        // 这是一个模拟输出， 在生产代码中你应该调用一个缓慢的 （相对于内存来说） 数据库查询结果。

        return 'Hello World!';
    }
}