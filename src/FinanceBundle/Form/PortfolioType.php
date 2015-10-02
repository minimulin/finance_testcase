<?php

namespace FinanceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FinanceBundle\Entity\Share;

class PortfolioType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('user')
            ->add('shares', 'entity', array(
                'class' => 'FinanceBundle:Share',
                'choice_label' => 'name',
                'multiple' => true
            ))
            ->add('save', 'submit')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FinanceBundle\Entity\Portfolio',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'financebundle_portfolio';
    }
}
