<?php
/**
 * Created by PhpStorm.
 * User: zcm
 * Date: 2018/8/7
 * Time: 16:12
 */

namespace Swoft\Encrypt\Aspect;

use Swoft\Aop\ProceedingJoinPoint;
use Swoft\App;
use Swoft\Bean\Annotation\Around;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\PointAnnotation;
use Swoft\Core\RequestContext;
use Swoft\Encrypt\Bean\Annotation\Encrypt;
use Swoft\Encrypt\Bean\Collector\EncryptCollector;
use Swoft\Encrypt\Handler\EncryptHandler;

/**
 * @Aspect()
 * @PointAnnotation(
 *      include={
 *          Encrypt::class
 *      }
 *  )
 * Class EncryptAspect
 * @package Swoft\Encrypt\Aspect
 */
class EncryptAspect
{
    private $classAnnotation;

    private $annotation;
    /**
     * @Around()
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     */
    public function around(ProceedingJoinPoint $proceedingJoinPoint)
    {
        list($this->classAnnotation, $this->annotation) = $this->getAnnotation($proceedingJoinPoint);

        /* @var EncryptHandler $encryptHandler*/
        $encryptHandler = App::getBean(EncryptHandler::class); // 因底层bug, 应注入EncryptHandlerInterface

        $before = $this->getAnnotationProperty('before');
        if ($before && method_exists($encryptHandler, $before)){
            $parsedBody = $encryptHandler->$before(request()->raw());
            if ($parsedBody){
                RequestContext::setRequest(request()->withParsedBody($parsedBody));
            }
        }

        $result = $proceedingJoinPoint->proceed(); // 后期兼容下参数注入

        $after = $this->getAnnotationProperty('after');
        if ($after && method_exists($encryptHandler, $after)){
            $result = $encryptHandler->$after($result);
        }

        return $result;
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return Encrypt[]
     */
    private function getAnnotation(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $collector = EncryptCollector::getCollector();
        $class_name = get_class($proceedingJoinPoint->getTarget());
        return [
            $collector[$class_name]['classAnnotation'],
            $collector[$class_name][$proceedingJoinPoint->getMethod()],
        ];
    }

    /**
     * 根据优先级取注解属性
     * @param string $field
     * @return mixed
     */
    public function getAnnotationProperty(string $field)
    {
        $method = 'get'.ucwords($field);
        return $this->annotation->$method()
            ?? $this->classAnnotation->$method()
            ?? App::getProperties()->get('encrypt')[$field];
    }
}