<?php


namespace spec\Ckr\Config;


use Ckr\Config\Config;
use Ckr\Config\Helper;

/**
 * Mocking objects with variadic methods does not work
 * in prophecy,so we use phpunit for this spec
 */
class HelperSpec extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $merger;

    /**
     * @var Helper
     */
    protected $helper;

    protected function setUp()
    {
        $this->merger = $this->getMockBuilder('Ckr\Config\ArrayMergerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helper = new Helper($this->merger);
    }

    /**
     * @test
     */
    public function its_mergeArrays_should_merge_arrays()
    {
        $arr1 = ['a' => 'b'];
        $arr2 = ['data'];
        $result = ['any result array'];

        $this->merger->expects($this->once())
            ->method('mergeRecursively')
            ->with($arr1, $arr2)
            ->willReturn($result);

        $this->assertSame($result, $this->helper->mergeArrays($arr1, $arr2));
    }

    /**
     * @test
     */
    public function its_mergeConfigs_should_merge_config_objects()
    {
        $arr1 = ['a' => 'a', 'b' => 'b'];
        $arr2 = ['data'];
        $arr3 = ['a' => 'aa'];
        $result = ['any result array'];

        $this->merger->expects($this->once())
            ->method('mergeRecursively')
            ->with($arr1, $arr2, $arr3)
            ->willReturn($result);

        $mergedConfig = $this->helper->mergeConfig(
            new Config($arr1),
            new Config($arr2),
            new Config($arr3)
        );
        $this->assertSame(
            $result,
            $mergedConfig->getAll()
        );
    }
} 