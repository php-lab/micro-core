<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core;

use PhpLab\Micro\Core\Fake\{Component, Service, Object, Logger, Counter};

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new Application();
        $this->app->component = function () {
            return new Component(function ($value) {
                return '#'.$value;
            });
        };
    }

    public function testShouldGetPresettedComponent()
    {
        $service = $this->app->component;
        $this->assertInstanceOf('\PhpLab\Micro\Core\Fake\Component', $service);
    }

    public function testShouldThrowExceptionIfServiceDefinitionNotFound()
    {
        $this->setExpectedException('\LogicException');
        $this->app->commonService;
    }

    public function testShouldGetService()
    {
        $this->app->commonService = function (Application $app) {
            return new Service($app->component);
        };
        $service = $this->app->commonService;
        $this->assertInstanceOf('\PhpLab\Micro\Core\Fake\Service', $service);
    }

    public function testShouldGetSameService()
    {
        $this->app->commonService = function (Application $app) {
            return new Service($app->component);
        };
        $instance1 = $this->app->commonService;
        $instance2 = $this->app->commonService;
        $this->assertSame($instance1, $instance2);
    }


    public function testShouldCallMethodOfComponentFromService()
    {
        $this->app->commonService = function (Application $app) {
            return new Service($app->component);
        };
        $service = $this->app->commonService;
        $result = $service->getComponent()->getResult('value');
        $this->assertEquals('#value', $result);
    }

    public function testShouldDefineServiceWithOptionalArgument()
    {
        $this->app->commonService = function (Application $app) {
            return new Service($app->component, 'xml');
        };
        $service = $this->app->commonService;
        $this->assertEquals('xml', $service->getFormat());
    }

    public function testShouldDefineServiceWithSetterInjection()
    {
        $this->app->commonService = function (Application $app) {
            $service = new Service($app->component);
            $service->setFormat('xml');

            return $service;
        };
        $service = $this->app->commonService;
        $this->assertEquals('xml', $service->getFormat());
    }

    public function testShouldGetServiceAfterChangeDefinition()
    {
        $this->app->commonService = function (Application $app) {
            return new Service($app->component);
        };
        $this->app->commonService = function (Application $app) {
            return new Service($app->component, 'xml');
        };
        $service = $this->app->commonService;
        $this->assertEquals('xml', $service->getFormat());
    }

    public function testShouldGetObject()
    {
        $this->app->_object = function () {
            return new Object();
        };
        $instance = $this->app->_object;
        $this->assertInstanceOf('\PhpLab\Micro\Core\Fake\Object', $instance);
    }

    public function testShouldGetNewInstanceOfObject()
    {
        $this->app->_object = function () {
            return new Object();
        };
        $instance1 = $this->app->_object;
        $instance2 = $this->app->_object;
        $this->assertNotSame($instance1, $instance2);
    }

    public function testShouldDefineObjectWithOptionalArgument()
    {
        $this->app->_object = function () {
            return new Object('value');
        };
        $instance = $this->app->_object;
        $this->assertEquals('value', $instance->getValue());
    }

    public function testShouldDefineObjectWithSetterInjection()
    {
        $this->app->_object = function () {
            $object = new Object();
            $object->setValue('value');

            return $object;
        };
        $instance = $this->app->_object;
        $this->assertEquals('value', $instance->getValue());
    }

    public function testShouldGetObjectAfterChangeDefinition()
    {
        $this->app->_object = function () {
            return new Object();
        };
        $this->app->_object = function () {
            return new Object('value');
        };
        $instance = $this->app->_object;
        $this->assertEquals('value', $instance->getValue());
    }

    public function testShouldThrowExceptionIfParameterNotFound()
    {
        $this->setExpectedException('\LogicException');
        $param = $this->app['param.not_exists'];
    }

    public function testShouldAssertWhatParameterNotExists()
    {
        $result = isset($this->app['param.not_exists']);
        $this->assertFalse($result);
    }

    public function testShouldAssertWhatParameterExists()
    {
        $this->app['param.test_value'] = 'value';
        $result = isset($this->app['param.test_value']);
        $this->assertTrue($result);
    }

    public function testShouldGetParameterValue()
    {
        $this->app['param.test_value'] = 'value';
        $param = $this->app['param.test_value'];
        $this->assertEquals('value', $param);
    }

    public function testShouldGetParameterAfterChangeValue()
    {
        $this->app['param.test_value'] = 'value';
        $this->app['param.test_value'] = 'another value';
        $param = $this->app['param.test_value'];
        $this->assertEquals('another value', $param);
    }

    public function testShouldRemoveParameter()
    {
        $this->app['param.test_value'] = 'value';
        unset($this->app['param.test_value']);
        $result = isset($this->app['param.test_value']);
        $this->assertFalse($result);
    }

    public function testShouldGetProtectedAnonymousFunction()
    {
        $this->app['func.protected'] = function () {
            return 'value';
        };
        $result = $this->app['func.protected']();
        $this->assertEquals('value', $result);
    }

    public function testShouldNotifyListener()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function () use ($logger) {
                $logger->log('Payment failure');
            }
        );
        $this->app->dispatch('payment.failure');
        $log = $logger->export();
        $this->assertEquals('Payment failure', $log[0]);
    }

    public function testShouldIgnoreOtherEvent()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function () use ($logger) {
                $logger->log('Payment failure');
            }
        );
        $this->app->dispatch('app.after_response');
        $log = $logger->export();
        $this->assertTrue(empty($log));
    }

    public function testShouldNotifyListenerAboutDifferentEvents()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function () use ($logger) {
                $logger->log('Payment failure');
            }
        );
        $this->app->subscribe(
            'app.critical_error',
            'logger',
            function () use ($logger) {
                $logger->log('Critical error');
            }
        );
        $this->app->dispatch('payment.failure');
        $this->app->dispatch('app.critical_error');
        $log = $logger->export();
        $this->assertEquals(['Payment failure', 'Critical error'], $log);
    }

    public function testShouldDispatchEventForDifferentListeners()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function () use ($logger) {
                $logger->log('Payment failure');
            }
        );
        $counter = new Counter();
        $this->app->subscribe(
            'payment.failure',
            'errorCounter',
            function () use ($counter) {
                $counter->increment();
            }
        );
        $this->app->dispatch('payment.failure');
        $log = $logger->export();
        $this->assertEquals('Payment failure', $log[0]);
        $this->assertEquals(1, $counter->total());
    }

    public function testShouldDispatchEventFromOtherSubscription()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function (Application $app) use ($logger) {
                $logger->log('Payment failure');
                $app->dispatch('payment.attempt');
            }
        );
        $counter = new Counter();
        $this->app->subscribe(
            'payment.attempt',
            'paymentCounter',
            function () use ($counter) {
                $counter->increment();
            }
        );
        $this->app->dispatch('payment.failure');
        $this->assertEquals(1, $counter->total());
    }
}
