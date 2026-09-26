<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use App\Support\NumericCaptcha;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestCommentTest extends TestCase
{
    private function makeNews(array $attributes = []): News
    {
        $category = Category::firstOrCreate(['slug' => 'umum'], ['name' => 'Umum', 'is_active' => true]);

        return News::create(array_merge([
            'user_id' => $this->superAdmin()->id,
            'category_id' => $category->id,
            'title' => 'Berita ' . Str::random(6),
            'content' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'allow_comments' => true,
        ], $attributes));
    }

    private function postGuestComment(News $news, array $overrides = [])
    {
        $code = NumericCaptcha::generate('komentar');

        return $this->post(route('komentar.store', $news->id), array_merge([
            'guest_name' => 'Budi Wali Murid',
            'guest_email' => 'budi@contoh.id',
            'content' => 'Kegiatan yang sangat bagus!',
            'captcha' => $code,
        ], $overrides));
    }

    public function test_guest_can_comment_and_it_waits_for_moderation(): void
    {
        $news = $this->makeNews();

        $this->postGuestComment($news)->assertRedirect()->assertSessionHas('comment_success');

        $comment = Comment::firstOrFail();
        $this->assertSame('pending', $comment->status);
        $this->assertSame('Budi Wali Murid', $comment->guest_name);
        $this->assertNull($comment->user_id);

        // Belum tampil sebelum disetujui…
        $this->get('/berita/' . $news->slug)->assertDontSee('Kegiatan yang sangat bagus!');

        // …dan tampil setelah disetujui moderator (email tidak pernah tampil).
        $comment->update(['status' => 'approved']);
        $this->get('/berita/' . $news->slug)
            ->assertSee('Kegiatan yang sangat bagus!')
            ->assertSee('Budi Wali Murid')
            ->assertDontSee('budi@contoh.id');
    }

    public function test_guest_comment_requires_correct_captcha(): void
    {
        $news = $this->makeNews();

        $this->postGuestComment($news, ['captcha' => '00000x'])->assertSessionHasErrors('captcha');

        $this->assertSame(0, Comment::count());
    }

    public function test_honeypot_silently_discards_bot_comments(): void
    {
        $news = $this->makeNews();

        $this->postGuestComment($news, ['website' => 'http://spam.example'])->assertSessionHas('comment_success');

        $this->assertSame(0, Comment::count());
    }

    public function test_comments_rejected_when_disabled_or_news_not_published(): void
    {
        $closed = $this->makeNews(['allow_comments' => false]);
        $this->postGuestComment($closed)->assertSessionHas('comment_error');

        $draft = $this->makeNews(['status' => 'draft']);
        $this->postGuestComment($draft)->assertNotFound();

        $this->assertSame(0, Comment::count());
    }

    public function test_staff_comment_is_approved_directly_without_captcha(): void
    {
        $news = $this->makeNews();

        $this->actingAs($this->userByEmail('moderator@test.local'))
            ->post(route('komentar.store', $news->id), ['content' => 'Terima kasih atas partisipasinya.'])
            ->assertSessionHas('comment_success');

        $this->assertSame('approved', Comment::firstOrFail()->status);
    }

    public function test_news_page_shows_guest_comment_form_with_captcha_image(): void
    {
        $news = $this->makeNews();

        $this->get('/berita/' . $news->slug)
            ->assertOk()
            ->assertSee('name="guest_name"', false)
            ->assertSee('kode-keamanan\/komentar', false)
            ->assertSee('Komentar akan tampil setelah disetujui moderator.');
    }
}
