<?php

namespace Bordeux\Bundle\CronBundle\Admin;

use Bordeux\Bundle\CronBundle\Command\CronCommand;
use Bordeux\Bundle\CronBundle\Entity\Cron;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;


/**
 * Class CronAdmin
 * @author Chris Bednarczyk <chris@tourradar.com>
 * @package Bordeux\Bundle\CronBundle\Admin
 */
class CronAdmin extends AbstractAdmin
{

    /**
     * @var string
     */
    protected $classnameLabel = "Cron";

    /**
     * @var string
     */
    protected $baseRouteName = "cron";

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {


        /** @var Cron $cron */
        $cron = $this->getSubject();


        $formMapper
            ->add("name")
            ->add("description")
            ->add("enabled")
            ->add('command', "choice", array(
                'choices' => array_flip($this->getCommandList()),
            ))
            ->add('arguments')
            ->add('interval')
            ->add('nextRunDate')
            ->setHelps(array(
                'interval' => "<a href='http://php.net/manual/pl/function.strtotime.php' target='_blank' rel='noopener noreferrer'>More about interval</a>",
            ));


    }


    /**
     * @return string[]
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    public function getCommandList()
    {
        $container = $this->getConfigurationPool()
            ->getContainer();

        $env = $container->get('kernel')
            ->getEnvironment();

        $consoleFile = CronCommand::getConsoleFilePath(
            $container->getParameter("kernel.root_dir")
        );


        $json = shell_exec("php {$consoleFile} list -e {$env} --format json ");

        $commandsList = json_decode($json, true);


        if (!is_array($commandsList)) {
            $commandsList = [];
        }

        if (!is_array($commandsList['commands'])) {
            $commandsList['commands'] = [];
        }

        $list = [];
        foreach ($commandsList['commands'] as $item) {
            if ($item["name"] == "xv:cron") {
                continue;
            }
            $list[$item["name"]] = implode(",", $item["usage"]);
        }

        return $list;
    }


    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridMapper
            ->add('name')
            ->add('command')
            ->add('arguments')
            ->add('interval')
            ->add('createDate')
            ->add('lastRunDate');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier("id")
            ->addIdentifier('name')
            ->addIdentifier('description')
            ->add('command')
            ->add('arguments')
            ->add('interval')
            ->add('createDate')
            ->add('lastRunDate', 'datetime')
            ->add('nextRunDate', 'datetime')
            ->add('enabled', 'boolean', [
                'editable' => true
            ])
            ->add('running', 'boolean', ['template' => 'BordeuxCronBundle::Sonata/status.html.twig'])
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )
            ));
    }


    /**
     * @param MenuItemInterface $menu
     * @param string $action
     * @param AdminInterface|null $childAdmin
     * @author Chris Bednarczyk <chris@tourradar.com>
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['show', 'edit'])) {
            return;
        }

        $admin = $this;
        $id = $admin->getRequest()->get('id');
        if (!$id) {
            return;
        }


        /** @var AdminInterface $child */
        foreach ($this->getChildren() as $child) {
            $menu->addChild(
                $child->getLabel(),
                [
                    'uri' => $this->generateUrl("{$child->getCode()}.list", [
                        'id' => $id
                    ])
                ]
            );
        }

    }


}
