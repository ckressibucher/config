<?php


namespace spec\Ckr\Config;


use Ckr\Config\DefaultArrayMerger;
use PhpSpec\ObjectBehavior;

class DefaultArrayMergerSpec extends ObjectBehavior
{

    public function it_should_return_empty_array_if_no_arguments()
    {
        $this->mergeRecursively()->shouldBeArray();
        $this->mergeRecursively()->shouldHaveCount(0);
    }

    public function it_should_return_a_copy_of_given_array_if_one_argument()
    {
        $this->mergeRecursively(['a' => 'val'])->shouldReturn(['a' => 'val']);
    }

    public function it_should_return_merge_a_second_array_with_precedence()
    {
        $this->mergeRecursively(['a' => 'default', 'b' => 'default'], ['a' => 'special'])
            ->shouldReturn(['a' => 'special', 'b' => 'default']);
    }

    public function it_should_merge_multiple_arrays()
    {
        $obj = new \stdClass();
        $arr1 = ['a' => 'a', 'b' => ['first', 'second']];
        $arr2 = ['a' => 1];
        $arr3 = ['b' => [1 => 'no-second'], 'c' => $obj];
        $expected = [
            'a' => 1,
            'b' => [0 => 'first', 1 => 'no-second'],
            'c' => $obj
        ];
        $this->mergeRecursively($arr1, $arr2, $arr3)->shouldReturn($expected);
    }

    public function its_mergeAllRecursively_should_merge_arrays_recursively()
    {
        $arr1 = ['a' => 'a', 'b' => ['first', 'second']];
        $arr2 = ['a' => 1];
        $arr3 = [];
        $arr4 = ['c' => null];
        $expected = [
            'a' => 1,
            'b' => ['first', 'second'],
            'c' => null
        ];
        $res = DefaultArrayMerger::mergeAllRecursively($arr1, $arr2, $arr3, $arr4);
        // use phpunit assertion for static methods
        try {
            \PHPUnit_Framework_Assert::assertEquals($expected, $res);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            throw new \Exception($e->getComparisonFailure()->getDiff());
        }
    }
} 