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

    function its_getConfig_should_return_the_given_config_array()
    {
        $config = array(
            'a' => array('b')
        );
        $this->beConstructedWith($config);
        $this->getConfig()->shouldReturn($config);
    }

    function its_getConfig_should_return_an_empty_array_if_constructed_without_arguments()
    {
        $config = array();
        $this->getConfig()->shouldReturn($config);
    }

    function its_getConfigValue_should_return_a_default_value_if_key_doesnt_exist()
    {
        $this->getConfigValue('inexistent_key', null)->shouldReturn(null);
        $this->getConfigValue('inexistent_key', false)->shouldReturn(false);
        $this->getConfigValue('inexistent_key')->shouldReturn(null);
    }

    function its_getConfigValue_should_return_an_existent_value()
    {
        $config = array('key' => 'value');
        $this->beConstructedWith($config);
        $this->getConfigValue('key')->shouldReturn('value');
    }

    function its_getConfigValue_should_return_a_nested_value_when_path_contains_a_slash()
    {
        $config = array(
            'outer' => array('inner' => 'value')
        );
        $this->beConstructedWith($config);
        $this->getConfigValue('outer/inner')->shouldReturn('value');
    }

    function its_getConfigValue_should_respect_escaped_slashes()
    {
        $config = array(
            'some/key' => 'flat array',
            '/begin' => 'begin',
            'end/' => 'end',
        );
        $this->beConstructedWith($config);

        $this->getConfigValue('some\/key')->shouldReturn('flat array');
        $this->getConfigValue('\/begin')->shouldReturn('begin');
        $this->getConfigValue('end\/')->shouldReturn('end');
    }

    function its_getConfigValue_should_handle_slashes_on_begin_and_end()
    {
        // edge cases on handling unescaped slashes
        $config = array(
            '' => array('key' => 'nested'),      // check path '/key'
            'key' => array('' => 'also nested'), // check paht 'key/'
        );
        $this->beConstructedWith($config);
        $this->getConfigValue('/key')->shouldReturn('nested');
        $this->getConfigValue('key/')->shouldReturn('also nested');
    }

    function its_getConfigValue_should_return_default_value_if_nested_value_doesnt_exist()
    {
        $config = array('outer' => 'x');
        $this->beConstructedWith($config);
        $this->getConfigValue('outer/a/b/c', null)->shouldReturn(null);
    }

    function its_getConfigValue_should_return_deep_nested_paths()
    {
        $config = array(
            'a' => array(
                'b' => array(
                    'c' => 'deep'
                )
            )
        );
        $this->beConstructedWith($config);
        $this->getConfigValue('a/b/c')->shouldReturn('deep');
    }

    function its_getConfigValue_should_throw_an_exception_if_path_is_empty_string()
    {
        $config = array();
        $this->beConstructedWith($config);
        $this->shouldThrow('\InvalidArgumentException')->during('getConfigValue', array(''));
    }

    function its_getChildConfig_should_return_a_config_object_for_child_data()
    {
        $innerConfig = array('a' => 'x');
        $config = array('outer' => $innerConfig);
        $this->beConstructedWith($config);
        $this->getChildConfig('outer')->shouldHaveType('Ckr\Config\Config');
        $this->getChildConfig('outer')->shouldHaveConfigData($innerConfig);
    }

    function its_getChildConfig_should_throw_an_exception_if_value_is_scalar()
    {
        $this->beConstructedWith(array('a' => 'x'));
        $this->shouldThrow('\Ckr\Config\InvalidStructureException')
            ->during('getChildConfig', array('a'));
    }

    function its_getChildConfig_should_throw_notFound_if_childConfig_is_not_found()
    {
        $this->beConstructedWith(array());
        $this->shouldThrow('Ckr\Config\NotFoundException')->during('getChildConfig', ['inexistent_key']);
    }

    function its_getChildConfig_should_return_empty_config_if_childConfig_is_not_found_and_tolerant_option_is_true()
    {
        $this->beConstructedWith(array());
        $this->getChildConfig('inexistent_key', true)->shouldHaveType('Ckr\Config\Config');
        $this->getChildConfig('inexistent_key', true)->shouldHaveConfigData([]);
    }

    function its_setConfigValue_should_set_a_scalar_value()
    {
        $this->beConstructedWith(array());
        $this->setConfigValue('path', 'value');

        $this->getConfigValue('path')->shouldReturn('value');
    }

    function its_setConfigValue_should_set_a_value_to_a_multipart_path()
    {
        $this->beConstructedWith(array());
        $this->setConfigValue('a/b/c', 'value');
        $this->getConfigValue('a/b/c')->shouldReturn('value');
    }

    function its_setConfigValue_should_set_a_subconfig()
    {
        $config = new Config(array('sub' => 'value'));
        $this->beConstructedWith(array());
        $this->setConfigValue('outer', $config);

        $this->getConfigValue('outer/sub')->shouldReturn('value');
    }

    public function getMatchers()
    {
        return array(
            'haveConfigData' => function ($subject, $expectedData) {
                $cfg = $subject->getConfig();
                return \is_array($expectedData)
                    && \sort($expectedData) == \sort($cfg);
            }
        );
    }
}
