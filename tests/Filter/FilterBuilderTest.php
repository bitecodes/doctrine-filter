<?php

namespace Fludio\DoctrineFilter\Tests;

use Fludio\DoctrineFilter\FilterBuilder;
use Fludio\DoctrineFilter\Tests\Dummy\Entity\Car;
use Fludio\DoctrineFilter\Tests\Dummy\Entity\Person;
use Fludio\DoctrineFilter\Tests\Dummy\Filter\TestFilter;
use Fludio\DoctrineFilter\Tests\Dummy\Fixtures\LoadPersonData;
use Fludio\DoctrineFilter\Tests\Dummy\Fixtures\LoadTransportData;
use Fludio\DoctrineFilter\Type\EqualFilterType;
use Fludio\DoctrineFilter\Type\GreaterThanEqualFilterType;
use Fludio\DoctrineFilter\Type\LikeFilterType;
use Fludio\DoctrineFilter\Tests\Dummy\Entity\Post;
use Fludio\DoctrineFilter\Tests\Dummy\Filter\PostFilter;
use Fludio\DoctrineFilter\Tests\Dummy\Fixtures\LoadCategoryData;
use Fludio\DoctrineFilter\Tests\Dummy\Fixtures\LoadPostData;
use Fludio\DoctrineFilter\Tests\Dummy\Fixtures\LoadTagData;
use Fludio\DoctrineFilter\Tests\Dummy\LoadFixtures;
use Fludio\DoctrineFilter\Tests\Dummy\TestCase;
use Fludio\DoctrineFilter\Tests\Dummy\Traits\TestFilterTrait;

class FilterBuilderTest extends TestCase
{
    use LoadFixtures, TestFilterTrait;

    public function loadFixtures()
    {
        return [
            new LoadCategoryData(),
            new LoadTagData(),
            new LoadPostData(),
            new LoadTransportData(),
            new LoadPersonData()
        ];
    }


    public function getFilterDefinition()
    {
        return function (FilterBuilder $builder) {
            $builder
                ->add('title', LikeFilterType::class)
                ->add('content', EqualFilterType::class)
                ->add('tags', EqualFilterType::class, [
                    'field' => 'tags.name'
                ])
                ->add('category', EqualFilterType::class, [
                    'field' => 'tags.category.name'
                ])
                ->add('blog', EqualFilterType::class, [
                    'field' => 'blog'
                ]);
        };
    }

    /** @test */
    public function it_returns_the_added_filters()
    {
        $builder = new FilterBuilder();
        $builder
            ->add('a', EqualFilterType::class)
            ->add('b', EqualFilterType::class);

        $this->assertCount(2, $builder->getFilters());
    }

    /** @test */
    public function it_tracks_parameters_by_their_own_value()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'title' => 'Post title',
            'content' => 'My post content!'
        ]);

        $this->assertCount(1, $posts);
        $this->assertEquals('Post title', $posts[0]->getTitle());
    }

    /** @test */
    public function it_queries_relationships()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'tags' => 'Tag 1',
        ]);

        $this->assertCount(1, $posts);
        $this->assertEquals('Tag 1', $posts[0]->getTags()->first()->getName());
    }

    /** @test */
    public function it_returns_no_results_if_relation_ship_query_is_not_fullfilled()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'tags' => 'Tag 4',
        ]);

        $this->assertCount(0, $posts);
    }

    /** @test */
    public function it_queries_deeply_nested_relationships()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'category' => 'Category 1',
        ]);

        $this->assertCount(1, $posts);
        $this->assertEquals('Category 1', $posts[0]->getTags()->first()->getCategory()->getName());
    }

    /** @test */
    public function it_returns_no_result_if_deeply_nested_relationship_query_is_not_fullfilled()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'category' => 'Category 2000',
        ]);

        $this->assertCount(0, $posts);
    }

    /** @test */
    public function it_can_handle_multiple_relationship_queries()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'title' => 'Post title',
            'tags' => 'Tag 2',
            'category' => 'Category 1'
        ]);

        $this->assertCount(1, $posts);

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'title' => 'Post title',
            'tags' => 'Tag 2',
            'category' => 'Category 2'
        ]);

        $this->assertCount(0, $posts);
    }

    /**
     * @test
     * @expectedException Doctrine\ORM\Query\QueryException
     */
    public function it_does_throw_an_exception_if_field_does_not_exist()
    {
        $this->em->getRepository(Post::class)->filter($this->filter, [
            'blog' => 'Blog A'
        ]);
    }

    /** @test */
    public function it_can_filter_on_embeddables()
    {
        $filter = new TestFilter();
        $filter->defineFilter(function (FilterBuilder $builder) {
            $builder
                ->add('horsepower', GreaterThanEqualFilterType::class, [
                    'field' => 'engine.horsepower'
                ]);
        });

        $res1 = $this->em->getRepository(Car::class)->filter($filter, [
            'horsepower' => 230
        ]);

        $this->assertEmpty($res1);

        $res2 = $this->em->getRepository(Car::class)->filter($filter, [
            'horsepower' => 210
        ]);

        $this->assertCount(1, $res2);
    }

    /** @test */
    public function it_can_filter_on_embeddables_on_relationships()
    {
        $filter = new TestFilter();
        $filter->defineFilter(function (FilterBuilder $builder) {
            $builder
                ->add('horsepower', GreaterThanEqualFilterType::class, [
                    'field' => 'cars.engine.horsepower'
                ]);
        });

        $res1 = $this->em->getRepository(Person::class)->filter($filter, [
            'horsepower' => 230
        ]);

        $this->assertEmpty($res1);

        $res2 = $this->em->getRepository(Person::class)->filter($filter, [
            'horsepower' => 210
        ]);

        $this->assertCount(1, $res2);
    }
}
