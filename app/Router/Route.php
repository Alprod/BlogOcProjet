<?php
namespace App\Router;



use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

class Route
{
    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string $name;
    /**
     * @var string
     */
    private string $path;

    /**
     * @var array|callable
     */
    private $callback;

    /**
     *  @param string $method
     *
     * @param string $name
     *
     * @param string $path
     *
     * @param array|callable $callback
     */
    public function __construct(string $method, string $name, string $path, $callback)
    {
        $this->method = $method;
        $this->name = $name;
        $this->path = $path;
        $this->callback = $callback;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMethode(): string
    {
        return $this->method;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function testMatchIds(string $path): bool
    {
        $basePath = str_replace("/", "\/", $this->path);

        $pattern = sprintf("/^%s$/", $basePath);

        $pattern = preg_replace("/(\{\w+\})/", "(.+)", $pattern);

        return preg_match($pattern, $path);
    }



    /**
     * @param string $path
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function call(string $path)
    {
        $callable = $this->callback;

        $basePath = str_replace("/", "\/", $this->path);

        $pattern = sprintf("/^%s$/", $basePath);

        $pattern = preg_replace("/(\{\w+\})/", "(.+)", $pattern);

        preg_match($pattern, $path, $matches);

        preg_match_all("/\{(\w+)\}/", $this->path, $paramMatches);

        array_shift($matches);

        $argsValue = [];

        $parameters = $paramMatches[1];

        if(count($parameters) > 0){
            $parameters = array_combine($parameters,$matches);
            if(is_array($this->callback)){
                $reflection = (new ReflectionClass($this->callback[0]))->getMethod($this->callback[1]);
            }else{
                $reflection = new ReflectionFunction($this->callback);
            }

            $args = array_map(fn ( ReflectionParameter $params ) => $params->getName(), $reflection->getParameters() );

            $argsValue = array_map(function (string $name) use ($parameters) {

                return $parameters[$name];

            }, $args);


        }

        if(is_array($callable)){
            $callable = [new $callable[0](), $callable[1]];
        }


        return call_user_func_array( $callable, $argsValue);
    }

}