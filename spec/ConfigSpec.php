<?php

namespace spec\Ckr\Config;

use Ckr\Config\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Process\Exception\RuntimeException;

class ConfigSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ckr\Config\Config');
    }

    function its_getAll_should_return_the_given_config_array()
    {
        $config = array(
            'a' => array('b')
        );
        $this->beConstructedWith($config);
        $this->getAll()->shouldReturn($config);
    }

    function its_getAll_should_return_an_empty_array_if_constructed_without_arguments()
    {
        $config = array();
        $this->getAll()->shouldReturn($config);
    }

    function its_get_should_return_a_default_value_if_key_doesnt_exist()
    {
        $this->get('inexistent_key', null)->shouldReturn(null);
        $this->get('inexistent_key', false)->shouldReturn(false);
        $this->get('inexistent_key')->shouldReturn(null);
    }

    function its_get_should_return_an_existent_value()
    {
        $config = array('key' => 'value');
        $this->beConstructedWith($config);
        $this->get('key')->shouldReturn('value');
    }

    function its_get_should_return_a_nested_value_when_path_contains_a_slash()
    {
        $config = array(
            'outer' => array('inner' => 'value')
        );
        $this->beConstructedWith($config);
        $this->get('outer/inner')->shouldReturn('value');
    }

    function its_get_should_respect_escaped_slashes()
    {
        $config = array(
            'some/key' => 'flat array',
            '/begin' => 'begin',
            'end/' => 'end',
        );
        $this->beConstructedWith($config);

        $this->get('some\/key')->shouldReturn('flat array');
        $this->get('\/begin')->shouldReturn('begin');
        $this->get('end\/')->shouldReturn('end');
    }

    function its_get_should_handle_slashes_on_begin_and_end()
    {
        // edge cases on handling unescaped slashes
        $config = array(
            '' => array('key' => 'nested'),      // check path '/key'
            'key' => array('' => 'also nested'), // check paht 'key/'
        );
        $this->beConstructedWith($config);
        $this->get('/key')->shouldReturn('nested');
        $this->get('key/')->shouldReturn('also nested');
    }

    function its_get_should_return_default_value_if_nested_value_doesnt_exist()
    {
        $config = array('outer' => 'x');
        $this->beConstructedWith($config);
        $this->get('outer/a/b/c', null)->shouldReturn(null);
    }

    function its_get_should_return_deep_nested_paths()
    {
        $config = array(
            'a' => array(
                'b' => array(
                    'c' => 'deep'
                )
            )
        );
        $this->beConstructedWith($config);
        $this->get('a/b/c')->shouldReturn('deep');
    }

    function its_get_should_throw_an_exception_if_path_is_empty_string()
    {
        $config = array();
        $this->beConstructedWith($config);
        $this->shouldThrow('\InvalidArgumentException')->during('get', array(''));
    }

    function its_child_should_return_a_config_object_for_child_data()
    {
        $innerConfig = array('a' => 'x');
        $config = array('outer' => $innerConfig);
        $this->beConstructedWith($config);
        $this->child('outer')->shouldHaveType('Ckr\Config\Config');
        $this->child('outer')->shouldHaveConfigData($innerConfig);
    }

    function its_child_should_throw_an_exception_if_value_is_scalar()
    {
        $this->beConstructedWith(array('a' => 'x'));
        $this->shouldThrow('\Ckr\Config\InvalidStructureException')
            ->during('child', array('a'));
    }

    function its_child_should_throw_notFound_if_childConfig_is_not_found()
    {
        $this->beConstructedWith(array());
        $this->shouldThrow('Ckr\Config\NotFoundException')->during('child', ['inexistent_key']);
    }

    function its_child_should_return_empty_config_if_childConfig_is_not_found_and_tolerant_option_is_true()
    {
        $this->beConstructedWith(array());
        $this->child('inexistent_key', true)->shouldHaveType('Ckr\Config\Config');
        $this->child('inexistent_key', true)->shouldHaveConfigData([]);
    }

    function its_set_should_set_a_scalar_value()
    {
        $this->beConstructedWith(array());
        $this->set('path', 'value')->shouldHaveAt('path', 'value');
    }

    function its_set_should_set_a_value_to_a_multipart_path()
    {
        $this->beConstructedWith(array());
        $this->set('a/b/c', 'value')->shouldHaveAt('a/b/c', 'value');
    }

    function its_set_should_set_a_subconfig()
    {
        $config = new Config(array('sub' => 'value'));
        $this->beConstructedWith(array());
        $this->set('outer', $config)->shouldHaveAt('outer/sub', 'value');
    }

    function its_set_should_leave_this_unmodified()
    {
        $this->beConstructedWith(['path' => 'old value']);
        $this->set('path', 'value')->shouldHaveAt('path', 'value');
        $this->get('path')->shouldReturn('old value');
    }

    function its_set_should_respect_escaped_slashes()
    {
        $this->beConstructedWith([]);
        $this->set('some\/key', 'value')->shouldHaveConfigData(['some/key' => 'value']);
    }

    public function getMatchers()
    {
        return array(
            'haveConfigData' => function ($subject, $expectedData) {
                $cfg = $subject->getAll();
                if (! is_array($expectedData)) {
                    return false;
                }
                sort($expectedData);
                sort($cfg);
                return $expectedData == $cfg;
            },
            'haveAt' => function($subject, $path, $expectedValue) {
                /* @var $subject Config */
                return $subject->get($path) === $expectedValue;
            }
        );
    }
}
