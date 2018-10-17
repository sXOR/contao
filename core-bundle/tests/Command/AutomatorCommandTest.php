<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Command;

use Contao\CoreBundle\Command\AutomatorCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class AutomatorCommandTest extends CommandTestCase
{
    public function testIsLockedWhileRunning(): void
    {
        $tmpDir = sys_get_temp_dir().'/'.md5($this->getFixturesDir());

        if (!is_dir($tmpDir)) {
            (new Filesystem())->mkdir($tmpDir);
        }

        $factory = new Factory(new FlockStore($tmpDir));

        $lock = $factory->createLock('contao:automator');
        $lock->acquire();

        $command = new AutomatorCommand($this->mockContaoFramework());
        $command->setApplication($this->mockApplication());

        $tester = new CommandTester($command);
        $tester->setInputs(["\n"]);

        $code = $tester->execute(['command' => $command->getName()]);

        $this->assertSame(1, $code);
        $this->assertContains('The command is already running in another process.', $tester->getDisplay());

        $lock->release();
    }

    public function testHandlesAnInvalidSelection(): void
    {
        $command = new AutomatorCommand($this->mockContaoFramework());
        $command->setApplication($this->mockApplication());

        $tester = new CommandTester($command);
        $tester->setInputs(["4800\n"]);

        $code = $tester->execute(['command' => $command->getName()]);

        $this->assertSame(1, $code);
        $this->assertContains('Value "4800" is invalid (see help contao:automator)', $tester->getDisplay());
    }

    public function testHandlesAnInvalidTaskName(): void
    {
        $command = new AutomatorCommand($this->mockContaoFramework());
        $command->setApplication($this->mockApplication());

        $input = [
            'command' => $command->getName(),
            'task' => 'fooBar',
        ];

        $tester = new CommandTester($command);
        $code = $tester->execute($input);

        $this->assertSame(1, $code);
        $this->assertContains('Invalid task "fooBar" (see help contao:automator)', $tester->getDisplay());
    }
}
