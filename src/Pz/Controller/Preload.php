<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Pz\Orm\AssetSize;
use Pz\Orm\Country;
use Pz\Orm\DataGroup;
use Pz\Orm\FragmentBlock;
use Pz\Orm\FragmentDefault;
use Pz\Orm\FragmentTag;
use Pz\Orm\Page;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Orm\User;
use Pz\Reader\Csv;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class Preload extends Controller
{

    /**
     * @route("/preload_pz", name="preload")
     * @return Response
     */
    public function preload()
    {

        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $csv = new Csv($this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/files/countries.csv');

        $presetCountries = array('NZ', 'AU', 'CN', 'US');

//        $data = Country::active($pdo);
//        foreach ($data as $itm) {
//            $itm->delete();
//        }
        $data = Country::active($pdo);
        if (!count($data)) {
            while ($row = $csv->getNextRow()) {
                if ($row[2]) {
                    $orm = new Country($pdo);
                    $orm->setTitle($row[1]);
                    $orm->setCode($row[2]);
                    $orm->setStatus(in_array($row[2], $presetCountries) ? 1 : 0);
                    $orm->save();
                }
            }
        }

        return new Response('OK');
    }
}