<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\QueueManager;
use EgalFramework\CommandLine\Tests\CommandTestCase;

class QueueManagerTest extends CommandTestCase
{

    public function testQueueManager()
    {
        /** @var QueueManager $command */
        $command = $this->getCommand(QueueManager::class);
        $this->input->setOption('kill', true);
        $this->input->setOption('create', true);
        $this->input->setOption('list', true);
        $command->handle();
        $this->assertTrue(true);
    }

}
