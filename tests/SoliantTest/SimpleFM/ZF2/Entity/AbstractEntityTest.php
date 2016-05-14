<?php
namespace SoliantTest\SimpleFM\ZF2\Entity;

use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\Exception\InvalidArgumentException;
use Soliant\SimpleFM\HostConnection;
use Soliant\SimpleFM\Loader\Mock as MockLoader;
use Soliant\SimpleFM\Result\FmResultSet;
use Soliant\SimpleFM\ZF2\Entity\AbstractEntity;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-06-28 at 11:25:01.
 */
class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $mockAdapterInstance;

    /**
     * @var MockLoader
     */
    protected $mockLoaderInstance;

    /**
     * @var AbstractEntity
     */
    protected $mockEntityInstanceEmpty;

    /**
     * @var AbstractEntity
     */
    protected $mockEntityInstance;

    /**
     * @var AbstractEntity
     */
    protected $mockEntityInstanceBad;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        /***********************************************************************************************************
         * Mock Adapter
         */
        $params = ['hostname' => 'localhost', 'dbname' => 'testdb', 'username' => 'Admin', 'password' => ''];
        $hostConnection = new HostConnection(
            $params['hostname'],
            $params['dbname'],
            $params['username'],
            $params['password']
        );
        $this->mockAdapterInstance = new Adapter($hostConnection);

        /***********************************************************************************************************
         * Mock Loader
         */

        $testXmlFile = file_get_contents(__DIR__ . '/../../TestAssets/sample_fmresultset.xml');
        $this->mockLoaderInstance = new MockLoader($testXmlFile);
        $this->mockAdapterInstance->setLoader($this->mockLoaderInstance);

        /** @var FmResultSet $result */
        $result = $this->mockAdapterInstance->execute();
        $rows = $result->getRows();

        /** *********************************************************************************************************
         * Mock Entity 1
         */

        $originalClassName = 'Soliant\SimpleFM\ZF2\Entity\AbstractEntity';
        $arguments = [];
        $mockClassName = 'MockEntity1';
        $callOriginalConstructor = false;
        $callOriginalClone = true;
        $callAutoload = true;
        $mockedMethods = [
            'getPropertyNameWriteable',
            'getPropertyNameReadOnly',
            'getFieldMapWriteable',
            'getFieldMapReadonly',
            'getDefaultWriteLayoutName',
            'getDefaultControllerRouteSegment',
        ];
        $cloneArguments = false;
        $this->mockEntityInstanceEmpty = $this->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getPropertyNameWriteable')
            ->will($this->returnValue('value'));

        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getPropertyNameReadOnly')
            ->will($this->returnValue('value2'));

        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getFieldMapWriteable')
            ->will($this->returnValue(['propertyNameWriteable' => 'fieldNameWriteable']));

        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getFieldMapReadonly')
            ->will($this->returnValue(['propertyNameReadOnly' => 'fieldNameReadOnly']));

        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getDefaultWriteLayoutName')
            ->will($this->returnValue('LayoutName'));

        $this->mockEntityInstanceEmpty->expects($this->any())
            ->method('getDefaultControllerRouteSegment')
            ->will($this->returnValue('route-segment'));

        $this->mockEntityInstanceEmpty->__construct();

        /** *********************************************************************************************************
         * Mock Entity 2
         */
        $mockClassName = 'MockEntity2';
        $mockedMethods[] = 'getName';
        $this->mockEntityInstance = $this->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
        $this->mockEntityInstance->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theNameValue'));

        $this->mockEntityInstance->expects($this->any())
            ->method('getPropertyNameWriteable')
            ->will($this->returnValue('value'));
        $this->mockEntityInstance->propertyNameWriteable = 'value';

        $this->mockEntityInstance->expects($this->any())
            ->method('getPropertyNameReadOnly')
            ->will($this->returnValue('value2'));
        $this->mockEntityInstance->propertyNameReadOnly = 'value2';

        $this->mockEntityInstance->expects($this->any())
            ->method('getFieldMapWriteable')
            ->will($this->returnValue(['propertyNameWriteable' => 'fieldNameWriteable']));

        $this->mockEntityInstance->expects($this->any())
            ->method('getFieldMapReadonly')
            ->will($this->returnValue(['propertyNameReadOnly' => 'fieldNameReadOnly']));

        $this->mockEntityInstance->expects($this->any())
            ->method('getDefaultWriteLayoutName')
            ->will($this->returnValue('LayoutName'));

        $this->mockEntityInstance->expects($this->any())
            ->method('getDefaultControllerRouteSegment')
            ->will($this->returnValue('route-segment'));

        $this->mockEntityInstance->__construct($rows[0]);

        /** *********************************************************************************************************
         * Mock Entity 3
         */
        $mockClassName = 'MockEntity3';
        $mockedMethods = [
            'getFieldMapWriteable',
            'getFieldMapMerged',
        ];
        $this->mockEntityInstanceBad = $this->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
        $this->mockEntityInstanceBad->expects($this->any())
            ->method('getFieldMapWriteable')
            ->will($this->returnValue(['propertyNameNonExistent' => 'fieldNameNonExistent']));
        $this->mockEntityInstanceBad->expects($this->any())
            ->method('getFieldMapMerged')
            ->will($this->returnValue(['propertyNameNonExistent' => 'fieldNameNonExistent']));

        $this->mockEntityInstanceBad->__construct();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::__construct
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::__toString
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::unserialize
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::unserializeField
     */
    public function testMagicMethods()
    {
        $this->assertEquals('', $this->mockEntityInstanceEmpty->getRecid());
        $this->assertEquals('46836', $this->mockEntityInstance->getRecid());

        $this->assertEquals('<toString is unconfigured>', $this->mockEntityInstanceEmpty->__toString());
        $this->assertEquals('theNameValue', $this->mockEntityInstance->__toString());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->mockEntityInstanceEmpty->unserialize();
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::getRecid
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::setRecid
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::getModid
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::setModid
     */
    public function testFluentGettersSetters()
    {
        $this->assertEquals(1, $this->mockEntityInstance->setRecid(1)->getRecid());
        $this->assertEquals(2, $this->mockEntityInstance->setModid(2)->getModid());
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::getFieldMapMerged
     */
    public function testGetFieldMapMerged()
    {
        $this->assertArrayHasKey('propertyNameWriteable', $this->mockEntityInstance->getFieldMapMerged());
        $this->assertArrayHasKey('propertyNameReadOnly', $this->mockEntityInstance->getFieldMapMerged());
    }


    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::serialize
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::serializeField
     */
    public function testSerialize()
    {
        $row = $this->mockEntityInstance->serialize();
        $this->assertArrayHasKey('-recid', $row);
        $this->assertArrayHasKey('-modid', $row);
        $this->assertArrayHasKey('fieldNameWriteable', $row);
        $this->assertArrayNotHasKey('fieldNameReadOnly', $row);
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::getArrayCopy
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::toArray
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::addPropertyToEntityAsArray
     */
    public function testGetArrayCopy()
    {
        $array = $this->mockEntityInstance->getArrayCopy();
        $this->assertArrayHasKey('recid', $array);
        $this->assertArrayHasKey('modid', $array);
        $this->assertArrayHasKey('propertyNameWriteable', $array);
        $this->assertArrayHasKey('propertyNameReadOnly', $array);
        $array = $this->mockEntityInstance->toArray();
        $this->assertArrayHasKey('recid', $array);
        $this->assertArrayHasKey('modid', $array);
        $this->assertArrayHasKey('propertyNameWriteable', $array);
        $this->assertArrayHasKey('propertyNameReadOnly', $array);
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::exchangeArray
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::getArrayCopy
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::addPropertyToEntityAsArray
     */
    public function testExchangeArray()
    {
        $array = $this->mockEntityInstance->getArrayCopy();
        $this->assertEquals('46836', $array['recid']);
        $this->assertEquals('1', $array['modid']);
        $this->assertEquals('value', $array['propertyNameWriteable']);
        $this->assertEquals('value2', $array['propertyNameReadOnly']);

        $array['recid'] = 111;
        $array['modid'] = 222;
        $array['propertyNameWriteable'] = 'newValue';
        // This one should not change, since it's read only
        $array['propertyNameReadOnly'] = 'newValue2';

        $entity = $this->mockEntityInstance->exchangeArray($array);
        $array2 = $entity->getArrayCopy();

        $this->assertEquals('111', $array2['recid']);
        $this->assertEquals('222', $array2['modid']);

        // We have to cheat on the two mock methods, and check the properties we added directly,
        // since the mock methods return a hardcoded value from the mock entity.
        $this->assertEquals('newValue', $entity->propertyNameWriteable);
        $this->assertEquals('value2', $entity->propertyNameReadOnly);
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::serializeField
     */
    public function testSerializeExceptions()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->mockEntityInstanceBad->serialize();
    }

    /**
     * @covers Soliant\SimpleFM\ZF2\Entity\AbstractEntity::addPropertyToEntityAsArray
     */
    public function testGetArrayCopyExceptions()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->mockEntityInstanceBad->getArrayCopy();
    }
}
