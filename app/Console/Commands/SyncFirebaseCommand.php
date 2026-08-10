<?php

namespace App\Console\Commands;

use App\Services\FirebaseMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Sign in to Firebase with import credentials, export key collections, import to MySQL.
 * Credentials come from env — never commit them.
 */
class SyncFirebaseCommand extends Command
{
    protected $signature = 'mateen:sync-firebase {--dry-run}';

    protected $description = 'Pull Firestore via REST and import into Mateen MySQL';

    public function handle(FirebaseMigrationService $service): int
    {
        $email = (string) (getenv('FIREBASE_IMPORT_EMAIL') ?: env('FIREBASE_IMPORT_EMAIL', ''));
        $password = (string) (getenv('FIREBASE_IMPORT_PASSWORD') ?: env('FIREBASE_IMPORT_PASSWORD', ''));
        $apiKey = (string) (getenv('FIREBASE_WEB_API_KEY') ?: env('FIREBASE_WEB_API_KEY', 'AIzaSyBIVtK3tNHSnQW6Pfq-cZgSvLmTX6kaeTk'));
        $projectId = (string) (getenv('FIREBASE_PROJECT_ID') ?: env('FIREBASE_PROJECT_ID', 'mateen-a122d'));

        // Fallback: read .env directly (config:cache makes env() empty)
        if ($email === '' || $password === '') {
            $envFile = base_path('.env');
            if (is_file($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (str_starts_with($line, 'FIREBASE_IMPORT_EMAIL=')) {
                        $email = trim(substr($line, strlen('FIREBASE_IMPORT_EMAIL=')));
                    }
                    if (str_starts_with($line, 'FIREBASE_IMPORT_PASSWORD=')) {
                        $password = trim(substr($line, strlen('FIREBASE_IMPORT_PASSWORD=')));
                    }
                }
            }
        }

        if ($email === '' || $password === '') {
            $this->error('Set FIREBASE_IMPORT_EMAIL and FIREBASE_IMPORT_PASSWORD in .env');

            return self::FAILURE;
        }

        try {
            $auth = Http::timeout(60)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key='.$apiKey,
                ['email' => $email, 'password' => $password, 'returnSecureToken' => true]
            );
            if (! $auth->successful()) {
                $this->error('Firebase auth failed: '.$auth->body());

                return self::FAILURE;
            }
            $idToken = $auth->json('idToken');
            $this->info('Firebase auth OK');

            $export = [];
            $collections = [
                'users', 'students', 'subjects', 'staticSubjects', 'materials', 'learningMaterials',
                'libraryItems', 'library', 'assignments', 'conversations', 'news', 'newsItems',
                'events', 'schedules', 'scheduleEntries', 'tokens', 'fcmTokens', 'devices',
                'contactMessages', 'contacts', 'registrationRequests', 'registrations',
            ];
            foreach ($collections as $name) {
                $export[$name] = $this->fetchCollection($projectId, $name, $idToken);
                $this->line("{$name}: ".count($export[$name]));
            }

            $conversationMessages = [];
            foreach ($export['conversations'] as $conv) {
                $cid = (string) ($conv['id'] ?? '');
                if ($cid === '') {
                    continue;
                }
                $msgs = $this->fetchCollection($projectId, 'conversations/'.$cid.'/messages', $idToken);
                if ($msgs) {
                    $conversationMessages[$cid] = $msgs;
                }
            }
            $export['conversationMessages'] = $conversationMessages;
            $this->line('conversation message threads: '.count($conversationMessages));

            $dir = storage_path('app/migration-fixtures');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = $dir.'/export-sync.json';
            file_put_contents($path, json_encode($export, JSON_UNESCAPED_UNICODE));

            $counts = $service->load($path, (bool) $this->option('dry-run'), [
                'admin_email' => $email,
                'admin_password' => $password,
            ]);
            $this->table(
                ['Collection', 'Records'],
                collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all()
            );
            @unlink($path);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line($e->getFile().':'.$e->getLine());
            if ($e->getPrevious()) {
                $this->error('prev: '.$e->getPrevious()->getMessage());
            }

            return self::FAILURE;
        }
    }

    /** @return list<array<string,mixed>> */
    private function fetchCollection(string $projectId, string $name, string $idToken): array
    {
        $docs = [];
        $pageToken = null;
        $base = 'https://firestore.googleapis.com/v1/projects/'.$projectId.'/databases/(default)/documents/'.$name;
        do {
            $url = $base.'?pageSize=300';
            if ($pageToken) {
                $url .= '&pageToken='.urlencode($pageToken);
            }
            $res = Http::timeout(90)->withToken($idToken)->get($url);
            if (! $res->successful()) {
                return $docs;
            }
            foreach ($res->json('documents') ?? [] as $doc) {
                $parts = explode('/', $doc['name'] ?? '');
                $id = end($parts);
                $row = ['id' => $id];
                foreach ($doc['fields'] ?? [] as $key => $value) {
                    $row[$key] = $this->decodeValue($value);
                }
                $docs[] = $row;
            }
            $pageToken = $res->json('nextPageToken');
        } while ($pageToken);

        return $docs;
    }

    private function decodeValue(mixed $v): mixed
    {
        if (! is_array($v)) {
            return $v;
        }
        if (array_key_exists('stringValue', $v)) {
            return $v['stringValue'];
        }
        if (array_key_exists('integerValue', $v)) {
            return (int) $v['integerValue'];
        }
        if (array_key_exists('doubleValue', $v)) {
            return (float) $v['doubleValue'];
        }
        if (array_key_exists('booleanValue', $v)) {
            return (bool) $v['booleanValue'];
        }
        if (array_key_exists('timestampValue', $v)) {
            return $v['timestampValue'];
        }
        if (array_key_exists('nullValue', $v)) {
            return null;
        }
        if (isset($v['arrayValue']['values'])) {
            return array_map(fn ($i) => $this->decodeValue($i), $v['arrayValue']['values']);
        }
        if (isset($v['mapValue']['fields'])) {
            $out = [];
            foreach ($v['mapValue']['fields'] as $k => $inner) {
                $out[$k] = $this->decodeValue($inner);
            }

            return $out;
        }
        if (isset($v['referenceValue'])) {
            return $v['referenceValue'];
        }

        return $v;
    }
}
