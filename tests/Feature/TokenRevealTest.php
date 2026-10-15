<?php

namespace Devtical\Sanctum\Tests\Feature;

use Devtical\Sanctum\Pages\Sanctum;
use Devtical\Sanctum\Tests\TestbenchTestCase;
use Devtical\Sanctum\Tests\TestUser;
use Filament\Actions\Action;
use Illuminate\Http\RedirectResponse;
use ReflectionMethod;

class TokenRevealTest extends TestbenchTestCase
{
    public function test_creating_a_token_keeps_the_plain_text_on_the_page(): void
    {
        $user = TestUser::query()->create([
            'name' => 'Test User',
            'email' => 'token-reveal@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $page = new Sanctum;
        $action = $this->newTokenAction($page);

        $result = $action->getActionFunction()([
            'name' => 'MCP client',
            'abilities' => ['users:read'],
            'expiration' => 'never',
        ]);

        $this->assertNull($result);
        $this->assertNotInstanceOf(RedirectResponse::class, $result);
        $this->assertTrue(filled($page->plainTextToken));
        $this->assertStringContainsString('|', $page->plainTextToken);
        $this->assertFalse(session()->has('sanctum-token'));
        $this->assertSame(1, $user->fresh()->tokens()->count());
        $this->assertSame('MCP client', $user->fresh()->tokens()->first()->name);
    }

    public function test_forget_plain_text_token_clears_the_secret(): void
    {
        $page = new Sanctum;
        $page->plainTextToken = '1|example-secret-token';

        $page->forgetPlainTextToken();

        $this->assertNull($page->plainTextToken);
    }

    public function test_token_banner_view_binds_livewire_property_instead_of_session_flash(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/pages/sanctum.blade.php');

        $this->assertStringContainsString('filled($plainTextToken)', $view);
        $this->assertStringContainsString('token: @js($plainTextToken)', $view);
        $this->assertStringContainsString('x-on:click="copy()"', $view);
        $this->assertStringContainsString('navigator.clipboard.writeText(this.token)', $view);
        $this->assertStringContainsString('wire:click="forgetPlainTextToken"', $view);
        $this->assertStringNotContainsString("session('sanctum-token')", $view);
        $this->assertStringNotContainsString('function copyToken', $view);
    }

    private function newTokenAction(Sanctum $page): Action
    {
        $method = new ReflectionMethod(Sanctum::class, 'getHeaderActions');
        $actions = $method->invoke($page);

        $action = collect($actions)->first(
            fn (Action $headerAction): bool => $headerAction->getName() === 'new',
        );

        $this->assertInstanceOf(Action::class, $action);

        return $action;
    }
}
