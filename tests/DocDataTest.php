<?php

namespace JouwWeb\DocData\Tests;

use JouwWeb\DocData\DocData;
use JouwWeb\DocData\Type;

use Psr\Log\LoggerInterface;

class DocDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocData
     */
    private $apiClient;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->apiClient = new DocData(
            MERCHANT_NAME, 
            MERCHANT_PASSWORD, 
            true,
            $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock()
        );
    }

    public function testSettersGetters()
    {
        $this->apiClient->setTimeOut(5);
        $this->assertEquals(5, $this->apiClient->getTimeOut());

        $this->apiClient->setUserAgent('testing/1.1.0');
        $this->assertEquals('testing/1.1.0', $this->apiClient->getUserAgent());
    }

    /**
     * @group functional
     */
    public function testCreate()
    {
        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $shopper = new Type\Shopper();
        $shopper->setId(1);
        $shopper->setGender('M');
        $shopper->setName($name);
        $shopper->setEmail('john@doe.com');
        $shopper->setLanguage(new Type\Language('nl'));

        $totalGrossAmount = new Type\Amount(2000);

        $address = new Type\Address();
        $address->setStreet('Coolsingel');
        $address->setHouseNumber(1);
        $address->setPostalCode('3020');
        $address->setCity('Rotterdam');
        $address->setCountry(new Type\Country('NL'));

        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $billTo = new Type\Destination();
        $billTo->setName($name);
        $billTo->setAddress($address);

        $paymentPreferences = new Type\PaymentPreferences();
        $paymentPreferences->setProfile('standard');
        $paymentPreferences->setNumberOfDaysToPay(4);

        $this
            ->logger
            ->expects($this->any())
            ->method('info')
            ->with($this->anything(), $this->callback(function ($message) {
                $representation = serialize($message);
                return stripos($representation, MERCHANT_PASSWORD) === false;
            }));

        $response = $this->apiClient->create(
            microtime(),
            $shopper,
            $totalGrossAmount,
            $billTo,
            $paymentPreferences
        );

        $this->assertInstanceOf('JouwWeb\DocData\Type\CreateSuccess', $response);
    }

    /**
     * @group functional
     */
    public function testCancel()
    {
        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $shopper = new Type\Shopper();
        $shopper->setId(1);
        $shopper->setGender('M');
        $shopper->setName($name);
        $shopper->setEmail('john@doe.com');
        $shopper->setLanguage(new Type\Language('nl'));

        $totalGrossAmount = new Type\Amount(2000);

        $address = new Type\Address();
        $address->setStreet('Coolsingel');
        $address->setHouseNumber(1);
        $address->setPostalCode('3021AB');
        $address->setCity('Rotterdam');
        $address->setCountry(new Type\Country('NL'));

        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $billTo = new Type\Destination();
        $billTo->setName($name);
        $billTo->setAddress($address);

        $paymentPreferences = new Type\PaymentPreferences();
        $paymentPreferences->setProfile('standard');
        $paymentPreferences->setNumberOfDaysToPay(4);

        $var = $this->apiClient->create(
            microtime(),
            $shopper,
            $totalGrossAmount,
            $billTo,
            $paymentPreferences
        );

        $response = $this->apiClient->cancel($var->getKey());

        $this->assertInstanceOf('\JouwWeb\DocData\Type\CancelSuccess', $response);
        $this->assertEquals('SUCCESS', $response->getSuccess()->getCode());
    }

    /**
     * @group functional
     */
    public function testCapture()
    {
        $this->markTestSkipped('we can\'t test this without using a fixed payment id, so alter the id below');
        $response = $this->apiClient->capture(4905992874);

        $this->assertInstanceOf('\JouwWeb\DocData\Type\CaptureSuccess', $response);
        $this->assertEquals('SUCCESS', $response->getSuccess()->getCode());
    }

    /**
     * @group functional
     */
    public function testRefund()
    {
        $this->markTestSkipped('we can\'t test this without using a fixed payment id, so alter the id below');
        $response = $this->apiClient->refund(4905992874);

        $this->assertInstanceOf('\JouwWeb\DocData\Type\RefundSuccess', $response);
        $this->assertEquals('SUCCESS', $response->getSuccess()->getCode());
    }

    /**
     * @group functional
     */
    public function testStatus()
    {
        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $shopper = new Type\Shopper();
        $shopper->setId(1);
        $shopper->setGender('M');
        $shopper->setName($name);
        $shopper->setEmail('john@doe.com');
        $shopper->setLanguage(new Type\Language('nl'));

        $totalGrossAmount = new Type\Amount(2000);

        $address = new Type\Address();
        $address->setStreet('Coolsingel');
        $address->setHouseNumber(1);
        $address->setPostalCode('3020AB');
        $address->setCity('Rotterdam');
        $address->setCountry(new Type\Country('NL'));

        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $billTo = new Type\Destination();
        $billTo->setName($name);
        $billTo->setAddress($address);

        $paymentPreferences = new Type\PaymentPreferences();
        $paymentPreferences->setProfile('standard');
        $paymentPreferences->setNumberOfDaysToPay(4);

        $var = $this->apiClient->create(
            microtime(),
            $shopper,
            $totalGrossAmount,
            $billTo,
            $paymentPreferences
        );

        $response = $this->apiClient->status($var->getKey());

        $this->assertInstanceOf('JouwWeb\DocData\Type\StatusSuccess', $response);
        $this->assertEquals('SUCCESS', $response->getSuccess()->getCode());
    }

    /**
     * @group functional
     */
    public function testStatusNotPaid()
    {
        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $shopper = new Type\Shopper();
        $shopper->setId(1);
        $shopper->setGender('M');
        $shopper->setName($name);
        $shopper->setEmail('john@doe.com');
        $shopper->setLanguage(new Type\Language('nl'));

        $totalGrossAmount = new Type\Amount(2000);

        $address = new Type\Address();
        $address->setStreet('Coolsingel');
        $address->setHouseNumber(1);
        $address->setPostalCode('3020AB');
        $address->setCity('Rotterdam');
        $address->setCountry(new Type\Country('NL'));

        $name = new Type\Name();
        $name->setFirst('John');
        $name->setLast('Doe');

        $billTo = new Type\Destination();
        $billTo->setName($name);
        $billTo->setAddress($address);

        $paymentPreferences = new Type\PaymentPreferences();
        $paymentPreferences->setProfile('standard');
        $paymentPreferences->setNumberOfDaysToPay(4);

        $var = $this->apiClient->create(
            microtime(),
            $shopper,
            $totalGrossAmount,
            $billTo,
            $paymentPreferences
        );

        $paidLevel = $this->apiClient->statusPaid($var->getKey());

        $this->assertEquals(Type\PaidLevel::NotPaid, $paidLevel);
    }

    /**
     * @group functional
     */
    public function testStatusPaid()
    {
        $this->markTestSkipped('we can\'t test this without a docdata transaction that has been paid.');

        //Please read the manual about the paidLevel
        $paidLevel = $this->apiClient->statusPaid('123ABD');

        $this->assertTrue(in_array($paidLevel, [Type\PaidLevel::BalancedRoute, Type\PaidLevel::SafeRoute]));

    }
}
