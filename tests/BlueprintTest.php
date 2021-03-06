<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-02
 */

namespace Runner\Heshen\Testing;

use Runner\Heshen\Blueprint;
use Runner\Heshen\Contracts\StatefulInterface;
use Runner\Heshen\Exceptions\LogicException;
use Runner\Heshen\Exceptions\StateNotFoundException;
use Runner\Heshen\Exceptions\TransitionNotFoundException;
use Runner\Heshen\State;
use Runner\Heshen\Support\StateEvents;

class BlueprintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Blueprint
     */
    protected $blueprint;

    public function setUp()
    {
        $this->blueprint = new class() extends Blueprint {
            protected function configure(): void
            {
                $this->addState('a', State::TYPE_INITIAL);
                $this->addState('b', State::TYPE_NORMAL);
                $this->addState('c', State::TYPE_FINAL);
                $this->addState('d', State::TYPE_FINAL);

                $this->addTransition('one', 'a', 'b');
                $this->addTransition('two', 'b', 'c');
                $this->addTransition('three', 'c', 'd');
            }

            protected function preOne(StatefulInterface $stateful, array $parameters)
            {
            }

            protected function postOne(StatefulInterface $stateful, array $parameters)
            {
            }
        };
    }

    public function testGetTransitionAndGetState()
    {
        $transition = $this->blueprint->getTransition('one');
        $this->assertSame(
            $this->blueprint->getState('a'),
            $transition->getFromStates()[0]
        );
        $this->assertSame(
            $this->blueprint->getState('b'),
            $transition->getToState()
        );
    }

    public function testGetDispatcher()
    {
        $this->assertSame(
            true,
            $this->blueprint->getDispatcher()->hasListeners(StateEvents::PRE_TRANSITION.'one')
        );
        $this->assertSame(
            true,
            $this->blueprint->getDispatcher()->hasListeners(StateEvents::POST_TRANSITION.'one')
        );
        $this->assertSame(
            false,
            $this->blueprint->getDispatcher()->hasListeners(StateEvents::PRE_TRANSITION.'two')
        );
        $this->assertSame(
            false,
            $this->blueprint->getDispatcher()->hasListeners(StateEvents::POST_TRANSITION.'two')
        );
    }

    public function testDeclareBlueprintWithoutConfigure()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('you must overwrite the configure method in the concrete blueprint class');

        new class() extends Blueprint {
        };
    }

    public function testGetNotExistState()
    {
        $this->expectException(StateNotFoundException::class);

        $this->blueprint->getState('hello');
    }

    public function testGetNotExistTransition()
    {
        $this->expectException(TransitionNotFoundException::class);

        $this->blueprint->getTransition('world');
    }
}
