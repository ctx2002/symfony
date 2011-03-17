<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Config;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FieldInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Form\EventListener\ObjectMapperListener;
use Symfony\Component\Form\Renderer\Plugin\FormPlugin;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FormConfig extends AbstractFieldConfig
{
    private $csrfProvider;

    public function __construct(FormFactoryInterface $factory,
            CsrfProviderInterface $csrfProvider)
    {
        parent::__construct($factory);

        $this->csrfProvider = $csrfProvider;
    }

    public function configure(FieldInterface $field, array $options)
    {
        $field->setValidationGroups($options['validation_groups'])
            ->setVirtual($options['virtual'])
            ->addRendererPlugin(new FormPlugin())
            ->addEventSubscriber(new ObjectMapperListener(
                $options['data_class'],
                $options['data_constructor']
            ), -10)
            // ObjectMapperListener modifies data, so set again
            ->setData($options['data']);

        if ($options['csrf_protection']) {
            $field->enableCsrfProtection($options['csrf_provider'], $options['csrf_field_name']);
        }
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'template' => 'form',
            'data_class' => null,
            'data_constructor' => null,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_provider' => $this->csrfProvider,
            'validation_groups' => null,
            'virtual' => false,
        );
    }

    public function createInstance($name)
    {
        return new Form($name, new EventDispatcher(), $this->getFormFactory(),
                $this->csrfProvider);
    }

    public function getParent(array $options)
    {
        return 'field';
    }

    public function getIdentifier()
    {
        return 'form';
    }
}