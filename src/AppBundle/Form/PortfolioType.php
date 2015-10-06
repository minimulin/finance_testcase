<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Share;

class PortfolioType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shares', 'entity', array(
                'class' => 'AppBundle:Share',
                'choice_label' => 'name',
                'label' => 'shares',
                'multiple' => true,
                'translation_domain' => 'app',
            ))
            ->add('save', 'submit', [
                'label' => 'Save',
                'translation_domain' => 'app'
            ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Portfolio',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_portfolio';
    }
}
