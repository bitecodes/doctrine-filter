<?php

namespace BiteCodes\DoctrineFilter\Tests\Filter\Type;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\DoctrineFilter\Tests\Dummy\Entity\Post;
use BiteCodes\DoctrineFilter\Tests\Dummy\LoadFixtures;
use BiteCodes\DoctrineFilter\Tests\Dummy\TestCase;
use BiteCodes\DoctrineFilter\Tests\Dummy\Traits\TestFilterTrait;
use BiteCodes\DoctrineFilter\Type\BooleanFilterType;

class BooleanFilterTypeTest extends TestCase
{
    use LoadFixtures, TestFilterTrait;

    public function getFilterDefinition()
    {
        return function (FilterBuilder $builder) {
            $builder
                ->add('isPublished', BooleanFilterType::class);
        };
    }

    /** @test */
    public function it_returns_entities_for_truthy_values()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '0',
        ]);

        $this->assertCount(1, $posts);

        $posts[0]->setIsPublished(true);
        $this->em->flush();

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '1',
        ]);

        $this->assertCount(1, $posts);
        $this->assertEquals('Post title with Tag 1', $posts[0]->getTitle());
    }

    /** @test */
    public function it_returns_entities_for_falsy_values()
    {
        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '0',
        ]);

        $this->assertCount(1, $posts);
        $this->assertEquals('Post title with Tag 1', $posts[0]->getTitle());

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '1',
        ]);

        $this->assertCount(0, $posts);
    }

    /** @test */
    public function truthy_and_falsy_values_can_be_set()
    {
        $this->filter->defineFilter(function (FilterBuilder $builder) {
            $builder
                ->add('isPublished', BooleanFilterType::class, [
                    'truthy_values' => ['true'],
                    'falsy_values'  => ['false'],
                ]);
        });

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => 'false',
        ]);

        $this->assertCount(1, $posts);

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => 'true',
        ]);

        $this->assertCount(0, $posts);

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '0',
        ]);

        // Still be 1, because filter gets skipped if value not defined
        $this->assertCount(1, $posts);

        $posts = $this->em->getRepository(Post::class)->filter($this->filter, [
            'isPublished' => '1',
        ]);

        // Still be 1, because filter gets skipped if value not defined
        $this->assertCount(1, $posts);
    }
}
