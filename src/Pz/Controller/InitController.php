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


class InitController extends Controller
{

    /**
     * @route("/init_pz")
     * @return Response
     */
    public function init()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $pdo->beginTransaction();

        $dir = $this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/src/Pz/Orm';
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated') {
                continue;
            }

            $className = "Pz\\Orm\\" . substr($file, 0, strrpos($file, '.'));
            $className::sync($pdo);
        }
        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated') {
                continue;
            }

            $className = "Pz\\Orm\\" . substr($file, 0, strrpos($file, '.'));
            $className::updateModel($pdo);
        }

        $pdo->commit();


        $pdo->beginTransaction();

        $this->addUsers($pdo);
        $this->addAssetSizes($pdo);
        $this->addPageCategories($pdo);
        $this->addPageTemplates($pdo);
        $this->addPages($pdo);
        $this->addFragmentTags($pdo);
        $this->addFragmentBlock($pdo);
        $this->addFragmentDefault($pdo);
        $this->addDataGroups($pdo);
        $this->addCountries($pdo);

        $pdo->commit();

        return new Response('OK');
    }

    /**
     * @param $pdo
     */
    public function addUsers($pdo)
    {
        $total = User::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $encoder = new MessageDigestPasswordEncoder();
            $orm = new User($pdo);
            $orm->setId(1);
            $orm->setTitle('weida');
            $orm->setPassword($encoder->encodePassword('123', ''));
            $orm->setName('Weida');
            $orm->setEmail('luckyweida@gmail.com');
            $orm->save(true);

            $orm = new User($pdo);
            $orm->setId(2);
            $orm->setTitle('admin');
            $orm->setPassword($encoder->encodePassword('dasiyebushuo', ''));
            $orm->setName('Administrator');
            $orm->setEmail('pozoltd@gmail.com');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addAssetSizes($pdo)
    {
        $total = AssetSize::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new AssetSize($pdo);
            $orm->setId(1);
            $orm->setTitle('full');
            $orm->setWidth(1440);
            $orm->save(true);

            $orm = new AssetSize($pdo);
            $orm->setId(2);
            $orm->setTitle('large');
            $orm->setWidth(960);
            $orm->save(true);

            $orm = new AssetSize($pdo);
            $orm->setId(3);
            $orm->setTitle('medium');
            $orm->setWidth(480);
            $orm->save(true);

            $orm = new AssetSize($pdo);
            $orm->setId(4);
            $orm->setTitle('small');
            $orm->setWidth(240);
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addPageCategories($pdo)
    {

        $total = PageCategory::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new PageCategory($pdo);
            $orm->setId(1);
            $orm->setTitle('Main nav');
            $orm->setCode('main');
            $orm->save(true);

            $orm = new PageCategory($pdo);
            $orm->setId(2);
            $orm->setTitle('Footer nav');
            $orm->setCode('footer');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addPageTemplates($pdo)
    {
        $total = PageTemplate::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new PageTemplate($pdo);
            $orm->setId(1);
            $orm->setTitle('layout.html.twig');
            $orm->setFilename('layout.html.twig');
            $orm->save(true);

            $orm = new PageTemplate($pdo);
            $orm->setId(2);
            $orm->setTitle('home.html.twig');
            $orm->setFilename('home.html.twig');
            $orm->save(true);

            $orm = new PageTemplate($pdo);
            $orm->setId(3);
            $orm->setTitle('about.html.twig');
            $orm->setFilename('about.html.twig');
            $orm->save(true);

            $orm = new PageTemplate($pdo);
            $orm->setId(4);
            $orm->setTitle('news.html.twig');
            $orm->setFilename('news.html.twig');
            $orm->save(true);

            $orm = new PageTemplate($pdo);
            $orm->setId(5);
            $orm->setTitle('news-detail.html.twig');
            $orm->setFilename('news-detail.html.twig');
            $orm->save(true);

            $orm = new PageTemplate($pdo);
            $orm->setId(6);
            $orm->setTitle('contact.html.twig');
            $orm->setFilename('contact.html.twig');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addPages($pdo)
    {
        $total = Page::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new Page($pdo);
            $orm->setId(1);
            $orm->setTitle('Home');
            $orm->setType(1);
            $orm->setTemplate(2);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/');
            $orm->setCategoryRank(json_encode(array("cat1" => 0)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);

            $orm = new Page($pdo);
            $orm->setId(2);
            $orm->setTitle('About');
            $orm->setType(1);
            $orm->setTemplate(3);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/about');
            $orm->setCategoryRank(json_encode(array("cat1" => 1)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);

            $orm = new Page($pdo);
            $orm->setId(3);
            $orm->setTitle('News');
            $orm->setType(1);
            $orm->setTemplate(4);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/news');
            $orm->setCategoryRank(json_encode(array("cat1" => 2)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);

            $orm = new Page($pdo);
            $orm->setId(4);
            $orm->setTitle('News detail');
            $orm->setType(1);
            $orm->setTemplate(5);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/news/detail');
            $orm->setCategoryRank(json_encode(array("cat1" => 0)));
            $orm->setCategoryParent(json_encode(array("cat1" => 3)));
            $orm->setStatus(2);
            $orm->save(true);

            $orm = new Page($pdo);
            $orm->setId(5);
            $orm->setTitle('Contact');
            $orm->setType(1);
            $orm->setTemplate(6);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/contact');
            $orm->setCategoryRank(json_encode(array("cat1" => 3)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addFragmentTags($pdo)
    {
        $total = FragmentTag::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new FragmentTag($pdo);
            $orm->setId(1);
            $orm->setTitle('Page');
            $orm->save(true);

            $orm = new FragmentTag($pdo);
            $orm->setId(2);
            $orm->setTitle('CMS');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addFragmentBlock($pdo)
    {
        $total = FragmentBlock::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new FragmentBlock($pdo);
            $orm->setId(1);
            $orm->setTitle('Heading & Content');
            $orm->setTwig('heading-content.twig');
            $orm->setTags('["1"]');
            $orm->setItems('[{"widget":"0","id":"heading","title":"Heading:","sql":""},{"widget":"5","id":"content","title":"Content:","sql":""}]');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addFragmentDefault($pdo)
    {
        $total = FragmentDefault::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new FragmentDefault($pdo);
            $orm->setId(1);
            $orm->setTitle('Page');
            $orm->setAttr('content');
            $orm->setContent('[{"id":"content","title":"Content:","tags":["1"]}]');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addDataGroups($pdo)
    {
        $total = DataGroup::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $orm = new DataGroup($pdo);
            $orm->setId(1);
            $orm->setTitle('Database');
            $orm->setIcon('fa fa-database');
            $orm->save(true);

            $orm = new DataGroup($pdo);
            $orm->setId(2);
            $orm->setTitle('Shop');
            $orm->setIcon('fa fa-shopping-cart');
            $orm->save(true);
        }
    }

    /**
     * @param $pdo
     */
    public function addCountries($pdo)
    {
        $total = Country::data($pdo, array(
            'count' => 1,
        ));
        if ($total['count'] == 0) {
            $csv = new Csv($this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/files/countries.csv');
            $row = $csv->getNextRow();
            while ($row = $csv->getNextRow()) {
                if ($row[2]) {
                    $orm = new Country($pdo);
                    $orm->setTitle($row[1]);
                    $orm->setCode($row[2]);
                    $orm->save();
                }
            }
        }
    }
}