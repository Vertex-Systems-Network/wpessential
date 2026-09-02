<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationDefinitionValidationService;
use WPEssential\Modules\Relations\RelationEndpointSupport;

final class RelationDefinitionValidationServiceTest extends TestCase
{
    public function testPublishedRegisteredNativeEndpointsAreAccepted(): void
    {
        $service = $this->service();
        $payload = $this->payload();
        $payload['to'] = [
            'object_type' => 'term',
            'object_subtype' => 'genre',
            'label' => 'Genres',
        ];

        $report = $service->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertTrue($report['valid']);
        self::assertSame([], $report['issues']);
        self::assertSame('book_authors', $report['candidate']['relation_key']);
        self::assertSame('post', $report['candidate']['from_type']);
        self::assertSame('term', $report['candidate']['to_type']);
    }

    public function testPublishedSafeExplicitRuntimeDefaultsAreAccepted(): void
    {
        $payload = $this->payload();
        $payload['storage_mode'] = 'shared_relation_table';
        $payload['storage_config'] = [
            'separate_table' => false,
            'table_name' => null,
            'index_strategy' => null,
            'foreign_keys' => false,
        ];
        $payload['pivot_enabled'] = false;
        $payload['pivot_policy'] = [
            'required_validation' => false,
            'queryable' => false,
            'index_policy' => null,
        ];
        $payload['multisite_scope'] = 'site';
        $payload['portability'] = ['definition' => true, 'edges' => false, 'pivot' => false];

        $report = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertTrue($report['valid']);
        self::assertSame([], $report['issues']);
    }

    public function testAdvancedPoliciesMayBeDraftedButUnsupportedRuntimeCannotPublish(): void
    {
        $payload = $this->payload();
        $payload['storage_mode'] = 'native_taxonomy_adapter';
        $payload['pivot_enabled'] = true;
        $payload['multisite_scope'] = 'network';
        $payload['portability'] = ['definition' => true, 'edges' => true, 'pivot' => false];

        $draft = $this->service()->validate([
            'payload' => $payload,
            'status' => 'draft',
        ]);
        $published = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertTrue($draft['valid']);
        self::assertFalse($published['valid']);
        self::assertSame([
            'relation.runtime.storage_mode.unsupported',
            'relation.runtime.pivot.unsupported',
            'relation.runtime.multisite_scope.unsupported',
            'relation.runtime.portability.unsupported',
        ], array_column($published['issues'], 'id'));
    }

    public function testExplicitPoliciesWithoutCertifiedEnforcementFailClosedAtPublish(): void
    {
        $payload = $this->payload();
        $payload['deletion_policy'] = [
            'delete_edges' => true,
            'from_object' => 'detach',
            'to_object' => 'detach',
        ];
        $payload['permissions_policy'] = ['connect' => 'edit_posts'];
        $payload['rest_policy'] = ['expose' => true, 'namespace' => 'wpessential/v1'];

        $report = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertFalse($report['valid']);
        self::assertSame([
            'relation.runtime.deletion_policy.unsupported',
            'relation.runtime.permissions_policy.unsupported',
            'relation.runtime.rest_policy.unsupported',
        ], array_column($report['issues'], 'id'));
    }

    public function testPublishedUnknownPostSubtypeFailsClosed(): void
    {
        $payload = $this->payload();
        $payload['from'] = [
            'object_type' => 'post',
            'object_subtype' => 'missing_type',
            'label' => 'Missing',
        ];

        $report = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('relation.endpoint.unsupported', $report['issues'][0]['id']);
        self::assertSame('from', $report['issues'][0]['field']);
        self::assertStringContainsString('missing_type', $report['issues'][0]['message']);
    }

    public function testCustomTableEndpointMayBeDraftedButCannotBePublishedBeforeAdapterCertification(): void
    {
        $payload = $this->payload();
        $payload['to'] = [
            'object_type' => 'custom_table',
            'object_subtype' => 'crm_contacts',
            'label' => 'Contacts',
        ];

        $draft = $this->service()->validate([
            'payload' => $payload,
            'status' => 'draft',
        ]);
        $published = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertTrue($draft['valid']);
        self::assertFalse($published['valid']);
        self::assertStringContainsString('separately certified Tables adapter', $published['issues'][0]['message']);
    }

    public function testRegisteredEntityEndpointCannotPublishBeforeAdapterCertification(): void
    {
        $payload = $this->payload();
        $payload['to'] = [
            'object_type' => 'registered_entity',
            'object_subtype' => 'external_customer',
            'label' => 'Customers',
        ];

        $report = $this->service()->validate([
            'payload' => $payload,
            'status' => 'published',
        ]);

        self::assertFalse($report['valid']);
        self::assertStringContainsString('separately certified endpoint adapter', $report['issues'][0]['message']);
    }

    public function testUserCommentAndMediaEndpointsRemainNativePublishable(): void
    {
        foreach (['user', 'comment', 'media'] as $type) {
            $payload = $this->payload();
            $payload['to'] = [
                'object_type' => $type,
                'label' => ucfirst($type),
            ];

            $report = $this->service()->validate([
                'payload' => $payload,
                'status' => 'published',
            ]);

            self::assertTrue($report['valid'], $type . ' endpoint should be publishable.');
        }
    }

    private function service(): RelationDefinitionValidationService
    {
        return new RelationDefinitionValidationService(
            new RelationDefinitionNormalizer(),
            new RelationEndpointSupport(
                static fn (string $postType): bool => $postType === 'book',
                static fn (string $taxonomy): bool => $taxonomy === 'genre',
            ),
        );
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'cardinality' => 'one_to_many',
            'from' => [
                'object_type' => 'post',
                'object_subtype' => 'book',
                'label' => 'Books',
            ],
            'to' => [
                'object_type' => 'user',
                'label' => 'Authors',
            ],
        ];
    }
}
