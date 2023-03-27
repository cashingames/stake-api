<?php

namespace App\Services\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    public function __construct(
        private readonly FirestoreClient $firestore
    ) {
    }

    public function setDocument(string $collection, string $document, array $data): void
    {
        $doc = $this->firestore->collection($collection)->document($document);
        $doc->set($data);
    }

    public function getDocument(string $collection, string $document): array
    {
        $doc = $this->firestore->collection($collection)->document($document);
        return $doc->snapshot()->data();
    }

    public function deleteDocument(string $collection, string $document): void
    {
        $doc = $this->firestore->collection($collection)->document($document);
        $doc->delete();
    }
}
