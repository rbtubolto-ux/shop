<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;
    private Campus $otherCampus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->campus      = Campus::create(['name' => 'Main', 'code' => 'MAIN', 'is_active' => true]);
        $this->otherCampus = Campus::create(['name' => 'B', 'code' => 'B', 'is_active' => true]);
    }

    private function user(string $role, ?Campus $campus = null): User
    {
        return User::create([
            'campus_id' => ($campus ?? $this->campus)->id,
            'name'      => $role,
            'email'     => uniqid($role).'@test.edu',
            'password'  => 'secret-password',
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    private function document(User $submitter, array $extra = []): Document
    {
        return Document::create(array_merge([
            'campus_id'    => $submitter->campus_id,
            'doc_number'   => uniqid('DOC'),
            'title'        => 'Test',
            'category'     => 'Memorandum',
            'direction'    => 'Incoming',
            'status'       => 'Draft',
            'submitted_by' => $submitter->id,
        ], $extra));
    }

    public function test_submitter_can_view_only_own_documents(): void
    {
        $submitter = $this->user('document_submitter');
        $other     = $this->user('document_submitter');
        $own       = $this->document($submitter);
        $foreign   = $this->document($other);

        $this->actingAs($submitter)->get(route('documents.show', $own))->assertOk();
        $this->actingAs($submitter)->get(route('documents.show', $foreign))->assertForbidden();
    }

    public function test_routing_unit_can_view_only_documents_routed_to_them(): void
    {
        $submitter = $this->user('document_submitter');
        $routing   = $this->user('routing_unit');
        $routed    = $this->document($submitter);
        $unrouted  = $this->document($submitter);

        DocumentRoute::create([
            'document_id' => $routed->id, 'step_order' => 1, 'step_name' => 'Budget',
            'assigned_to' => $routing->id, 'action' => 'Pending',
        ]);

        $this->actingAs($routing)->get(route('documents.show', $routed))->assertOk();
        $this->actingAs($routing)->get(route('documents.show', $unrouted))->assertForbidden();
    }

    public function test_users_from_another_campus_cannot_view_documents(): void
    {
        $submitter = $this->user('document_controller');
        $doc       = $this->document($submitter);
        $outsider  = $this->user('document_controller', $this->otherCampus);

        $this->actingAs($outsider)->get(route('documents.show', $doc))->assertForbidden();
    }

    public function test_only_managers_or_owner_submitter_can_edit(): void
    {
        $submitter = $this->user('document_submitter');
        $other     = $this->user('document_submitter');
        $routing   = $this->user('routing_unit');
        $controller = $this->user('document_controller');
        $doc       = $this->document($submitter);

        $payload = ['title' => 'Edited', 'category' => 'Memorandum', 'direction' => 'Incoming'];

        $this->actingAs($other)->put(route('documents.update', $doc), $payload)->assertForbidden();
        $this->actingAs($routing)->put(route('documents.update', $doc), $payload)->assertForbidden();
        $this->actingAs($submitter)->put(route('documents.update', $doc), $payload)->assertRedirect();
        $this->actingAs($controller)->put(route('documents.update', $doc), $payload)->assertRedirect();

        $this->assertSame('Edited', $doc->fresh()->title);
    }

    public function test_only_managers_or_owner_can_delete(): void
    {
        $submitter = $this->user('document_submitter');
        $other     = $this->user('document_submitter');
        $doc       = $this->document($submitter);

        $this->actingAs($other)->delete(route('documents.destroy', $doc))->assertForbidden();
        $this->assertNotNull(Document::find($doc->id));

        $this->actingAs($submitter)->delete(route('documents.destroy', $doc))->assertRedirect();
        $this->assertNull(Document::find($doc->id));
    }

    public function test_file_download_is_authorized_and_served_from_private_disk(): void
    {
        Storage::fake('local');

        $submitter = $this->user('document_submitter');
        $other     = $this->user('document_submitter');
        $doc       = $this->document($submitter, [
            'file_path' => 'documents/abc.pdf',
            'file_name' => 'letter.pdf',
        ]);
        Storage::disk('local')->put('documents/abc.pdf', 'PDF-BYTES');

        $this->actingAs($submitter)->get(route('documents.file', $doc))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($other)->get(route('documents.file', $doc))->assertForbidden();
    }

    public function test_file_route_returns_404_when_no_file(): void
    {
        $submitter = $this->user('document_submitter');
        $doc       = $this->document($submitter);

        $this->actingAs($submitter)->get(route('documents.file', $doc))->assertNotFound();
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        $this->user('document_submitter');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'nobody@test.edu', 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => 'nobody@test.edu', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $user = $this->user('document_submitter');
        $this->actingAs($user);

        $user->update(['is_active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
