<?php

namespace Dothiv\Bundle\ContentfulBundle\Command;

use Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface;
use Dothiv\Bundle\ContentfulBundle\Entity\Config;
use PhpOption\Option;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to manipulate configuration settings
 */
class ConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:config')
            ->setDescription('Manipulate configurations settings.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the configuration setting')
            ->addArgument('value', InputArgument::OPTIONAL, 'Value of the configuration setting to set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name  = $input->getArgument('name');
        $value = $input->getArgument('value');
        if (Option::fromValue($name)->isEmpty()) {
            $this->listConfigSettings($output);
            return;
        }
        if (Option::fromValue($value)->isEmpty()) {
            $this->listConfig($output, $name);
            return;
        }
        $this->updateConfig($input, $name, $value);
        $this->listConfig($output, $name);
    }

    protected function listConfigSettings(OutputInterface $output)
    {
        $configs = $this->getConfigRepo()->findAll();
        $this->showTable($output, $configs);
    }

    protected function listConfig(OutputInterface $output, $name)
    {
        $this->showTable($output, array($this->getConfigRepo()->get($name)));
    }

    protected function updateConfig(InputInterface $input, $name, $value)
    {
        $config = $this->getConfigRepo()->get($name);
        $config->setValue($value);
        $this->getConfigRepo()->persist($config)->flush();
    }

    /**
     * @return ConfigRepositoryInterface
     */
    protected function getConfigRepo()
    {
        return $this->getContainer()->get('dothiv_contentful.repo.config');
    }

    /**
     * @param OutputInterface $output
     * @param                 $configs
     */
    protected function showTable(OutputInterface $output, $configs)
    {
        $table = new TableHelper();
        $table->setHeaders(array('Name', 'Value'));
        foreach ($configs as $config) {
            /** @var $config Config */
            $table->addRow(array($config->getName(), $config->getValue()));
        }
        $table->render($output);
    }
}
