<?php

namespace FinanceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FinanceBundle\Entity\Share;

class LoadSharesData implements FixtureInterface
{
    private $data = [
        'YHOO' => 'Yahoo',
        'Apple' => 'AAPL',
        'Google' => 'GOOG',
        'Яндекс' => 'YNDX',
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $code => $share_name) {
            $share = new Share();
            $share->setName($share_name);
            $share->setCode($code);

            $manager->persist($share);
        }
        
        $manager->flush();
    }
}
