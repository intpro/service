<?php

namespace Interpro\Service\Test;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase;
use Interpro\QS\Model\Block;
use Interpro\QS\Model\Group;
use Interpro\Scalar\Model\String;
use Intervention\Image\Facades\Image;

//php vendor/bin/phpunit --verbose ./packages/interpro/Service
class CleanTest extends TestCase
{
    use DatabaseMigrations;

    private $mediator;
    private $syncAgent;
    private $initAgent;
    private $extractAgent;

    /**
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->mediator = $this->app->make('Interpro\Service\Contracts\CleanMediator');
        $this->syncAgent = $this->app->make('Interpro\Entrance\Contracts\CommandAgent\SyncAgent');
        $this->initAgent = $this->app->make('Interpro\Entrance\Contracts\CommandAgent\InitAgent');
        $this->extractAgent = $this->app->make('Interpro\Entrance\Contracts\Extract\ExtractAgent');
    }

    public function tearDown(): void
    {
       parent::tearDown();
    }

    public function testClean()
    {
        $this->syncAgent->syncAll();


        //Набьем элементами группы и проверим соответствие выборки ожиданиям
        $pinguinItem = $this->initAgent->init('group_bird_type', ['slug' => 'pinguin', 'descr' => 'Все представители этого семейства хорошо плавают и ныряют.', 'title' => 'Пингвиновые']);
        $chickenItem = $this->initAgent->init('group_bird_type', ['slug' => 'chicken', 'descr' => 'У них крепкие лапы, приспособленные для быстрого бега и рытья земли.', 'title' => 'Курообразные']);
        $duckItem = $this->initAgent->init('group_bird_type',    ['slug' => 'duck', 'descr' => 'Гуси, утки, лебеди.', 'title' => 'Гусеобразные']);

        //Области обитания
        $areaTropicItem = $this->initAgent->init('group_area', ['slug' => 'tropic', 'descr' => 'Влажные леса на разных континентах.', 'title' => 'Тропики']);
        $areaNordItem = $this->initAgent->init('group_area', ['slug' => 'nord', 'descr' => 'Северные широты евразии и америки.', 'title' => 'Север']);
        $areaAntarcticItem = $this->initAgent->init('group_area', ['slug' => 'antarctic', 'descr' => 'Антарктида и острова вокруг.', 'title' => 'Антарктика']);

        //Виды птиц
        $pinguinImperorItem = $this->initAgent->init('group_bird_class', ['superior' => $pinguinItem->id, 'slug' => 'imperor_pinguin', 'descr' => 'Самый крупный и тяжёлый из современных видов семейства.', 'title' => 'Императорский']);
        $pinguinAdeliItem   = $this->initAgent->init('group_bird_class', ['superior' => $pinguinItem->id, 'slug' => 'adeli_pinguin', 'descr' => 'Один из самых распространенных видов.', 'title' => 'Адели']);

        $chickenTeterevItem = $this->initAgent->init('group_bird_class', ['superior' => $chickenItem->id, 'slug' => 'teterev_chicken', 'descr' => 'Оседлая либо кочующая птица.', 'title' => 'Тетерев']);
        $chickenFazanItem   = $this->initAgent->init('group_bird_class', ['superior' => $chickenItem->id, 'slug' => 'fazan_chicken', 'descr' => 'Все признаки для фазановых в равной мере характерны, как и для курообразных вообще.', 'title' => 'Фазан']);

        $duckGusItem = $this->initAgent->init('group_bird_class', ['superior' => $duckItem->id, 'slug' => 'gus_duck', 'descr' => 'Гуси отличаются клювом, имеющим при основании большую высоту, чем ширину, и оканчивающимся ноготком с острым краем.', 'title' => 'Гусь']);
        $duckDuckItem = $this->initAgent->init('group_bird_class', ['superior' => $duckItem->id, 'slug' => 'duck_duck', 'descr' => 'Птицы средних и небольших размеров с относительно короткой шеей.', 'title' => 'Утка']);


        $newGroup = new Block();
        $newGroup->name = 'wrongblock';
        $newGroup->save();

        $newGroup = new Group();
        $newGroup->name = 'group_bird_type1';
        $newGroup->save();

        $stringField = new String();
        $stringField->entity_name = 'group_bird_type';
        $stringField->entity_id = $areaAntarcticItem->id;
        $stringField->name = 'wrongfield1';
        $stringField->value = 'blabla';
        $stringField->save();

        $stringField = new String();
        $stringField->entity_name = 'group_bird_type';
        $stringField->entity_id = $areaAntarcticItem->id;
        $stringField->name = 'wrongfield2';
        $stringField->value = 'blablabla';
        $stringField->save();

        $file1 = public_path('images/test/crops').'/wrongfile1.png';
        $file2 = public_path('images/test/crops').'/wrongfile2.png';

        $img = Image::canvas(50, 50, '#ff0000')->encode('png', 100);
        $img->save($file1, 100);

        $img = Image::canvas(50, 50, '#00ff00')->encode('png', 100);
        $img->save($file2, 100);

        chmod($file1, 0644);
        chmod($file2, 0644);


        //---------------------------------------------
        $this->mediator->clean();
        //---------------------------------------------


        $this->assertFileNotExists($file1);
        $this->assertFileNotExists($file2);

        $must_be = '';

        $must_be .= 'pinguin:(descr:Все представители этого семейства хорошо плавают и ныряют.,title:Пингвиновые),';
        $must_be .= 'chicken:(descr:У них крепкие лапы, приспособленные для быстрого бега и рытья земли.,title:Курообразные),';
        $must_be .= 'duck:(descr:Гуси, утки, лебеди.,title:Гусеобразные),';

        $must_be .= 'tropic:(descr:Влажные леса на разных континентах.,title:Тропики),';
        $must_be .= 'nord:(descr:Северные широты евразии и америки.,title:Север),';
        $must_be .= 'antarctic:(descr:Антарктида и острова вокруг.,title:Антарктика),';

        $must_be .= 'imperor_pinguin:(descr:Самый крупный и тяжёлый из современных видов семейства.,title:Императорский,superior:'.$pinguinItem->id.'),';
        $must_be .= 'adeli_pinguin:(descr:Один из самых распространенных видов.,title:Адели,superior:'.$pinguinItem->id.'),';

        $must_be .= 'teterev_chicken:(descr:Оседлая либо кочующая птица.,title:Тетерев,superior:'.$chickenItem->id.'),';
        $must_be .= 'fazan_chicken:(descr:Все признаки для фазановых в равной мере характерны, как и для курообразных вообще.,title:Фазан,superior:'.$chickenItem->id.'),';

        $must_be .= 'gus_duck:(descr:Гуси отличаются клювом, имеющим при основании большую высоту, чем ширину, и оканчивающимся ноготком с острым краем.,title:Гусь,superior:'.$duckItem->id.'),';
        $must_be .= 'duck_duck:(descr:Птицы средних и небольших размеров с относительно короткой шеей.,title:Утка,superior:'.$duckItem->id.'),';


        $we_have = '';

        $we_have .= $pinguinItem->slug.':(descr:'.$pinguinItem->descr.',title:'.$pinguinItem->title.'),';
        $we_have .= $chickenItem->slug.':(descr:'.$chickenItem->descr.',title:'.$chickenItem->title.'),';
        $we_have .= $duckItem->slug.':(descr:'.$duckItem->descr.',title:'.$duckItem->title.'),';

        $we_have .= $areaTropicItem->slug.':(descr:'.$areaTropicItem->descr.',title:'.$areaTropicItem->title.'),';
        $we_have .= $areaNordItem->slug.':(descr:'.$areaNordItem->descr.',title:'.$areaNordItem->title.'),';
        $we_have .= $areaAntarcticItem->slug.':(descr:'.$areaAntarcticItem->descr.',title:'.$areaAntarcticItem->title.'),';

        $we_have .= $pinguinImperorItem->slug.':(descr:'.$pinguinImperorItem->descr.',title:'.$pinguinImperorItem->title.',superior:'.$pinguinItem->id.'),';
        $we_have .= $pinguinAdeliItem->slug.':(descr:'.$pinguinAdeliItem->descr.',title:'.$pinguinAdeliItem->title.',superior:'.$pinguinItem->id.'),';

        $we_have .= $chickenTeterevItem->slug.':(descr:'.$chickenTeterevItem->descr.',title:'.$chickenTeterevItem->title.',superior:'.$chickenItem->id.'),';
        $we_have .= $chickenFazanItem->slug.':(descr:'.$chickenFazanItem->descr.',title:'.$chickenFazanItem->title.',superior:'.$chickenItem->id.'),';

        $we_have .= $duckGusItem->slug.':(descr:'.$duckGusItem->descr.',title:'.$duckGusItem->title.',superior:'.$duckItem->id.'),';
        $we_have .= $duckDuckItem->slug.':(descr:'.$duckDuckItem->descr.',title:'.$duckDuckItem->title.',superior:'.$duckItem->id.'),';



        $this->assertEquals(
            $we_have, $must_be
        );

        $birdsClasses = $this->extractAgent->countGroup('group_bird_class');
        $birdsTypes = $this->extractAgent->countGroup('group_bird_type');
        $areas = $this->extractAgent->countGroup('group_area');

        $this->assertEquals(6, $birdsClasses);
        $this->assertEquals(3, $birdsTypes);
        $this->assertEquals(3, $areas);

        //Пронумеровался ли сортировщик?
        $this->assertEquals(1, $pinguinImperorItem->sorter);
        $this->assertEquals(2, $pinguinAdeliItem->sorter);

        $this->assertEquals(1, $chickenTeterevItem->sorter);
        $this->assertEquals(2, $chickenFazanItem->sorter);

        $this->assertEquals(1, $duckGusItem->sorter);
        $this->assertEquals(2, $duckDuckItem->sorter);


        $this->extractAgent->reset();
        $selection = $this->extractAgent->selectGroup('group_bird_type');

        $maxid = $selection->get()->maxByField('sorter');
        $minid = $selection->get()->minByField('sorter');
        $sumid = $selection->get()->sumByField('sorter');

        $this->assertEquals(3, $maxid->sorter);
        $this->assertEquals(1, $minid->sorter);
        $this->assertEquals(6, $sumid);

    }


}
