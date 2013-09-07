<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Tests\Type;

use Prunatic\WebBundle\Entity\Point;
use Prunatic\WebBundle\Type\PointType;
use Doctrine\DBAL\Types\Type;

class PointTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    protected $platform;

    /**
     * @var PointType
     */
    protected $type;

    public static function setUpBeforeClass()
    {
        Type::addType('point', 'Prunatic\WebBundle\Type\PointType');
    }

    protected function setUp()
    {
        $this->platform = $this->getMockForAbstractClass('\Doctrine\DBAL\Platforms\AbstractPlatform');
        $this->type = Type::getType('point');
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::getSqlDeclaration
     */
    public function testReturnsSqlDeclaration()
    {
        $this->assertEquals("POINT", $this->type->getSqlDeclaration(array(), $this->platform));
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::convertToPHPValue
     */
    public function testConvertToPHPValue()
    {
        $longitude = 1;
        $latitude = 2;
        $databaseValue = sprintf('POINT(%F %F)', $longitude, $latitude);

        $point = $this->type->convertToPHPValue($databaseValue, $this->platform);
        $this->assertTrue($point instanceof Point);
        $this->assertEquals($longitude, $point->getLongitude());
        $this->assertEquals($latitude, $point->getLatitude());
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::convertToDatabaseValue
     */
    public function testConvertToDatabaseValue()
    {
        $longitude = 1;
        $latitude = 2;
        $point = new Point(1,2);

        $databaseValue = sprintf('POINT(%F %F)', $longitude, $latitude);

        $this->assertEquals($databaseValue, $this->type->convertToDatabaseValue($point, $this->platform));
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::canRequireSQLConversion
     */
    public function testReturnCanRequireSQLConversion()
    {
        $this->assertTrue($this->type->canRequireSQLConversion());
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::convertToPHPValueSQL
     */
    public function testReturnsConvertToPHPValueSQL()
    {
        $this->assertEquals('AsText(x)', $this->type->convertToPHPValueSQL('x', $this->platform));
    }

    /**
     * @covers \Prunatic\WebBundle\Type\PointType::convertToDatabaseValueSQL
     */
    public function testReturnsConvertToDatabaseValueSQL()
    {
        $this->assertEquals('PointFromText(x)', $this->type->convertToDatabaseValueSQL('x', $this->platform));
    }

}
