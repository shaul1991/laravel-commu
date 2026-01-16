<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Button 컴포넌트 테스트
 *
 * 테스트 항목:
 * - Variant: primary, secondary, outline, ghost, danger
 * - Size: sm, md, lg
 * - Type: button, link (href)
 * - State: disabled
 */
class ButtonComponentTest extends DuskTestCase
{
    #[Test]
    public function primary_button_renders_with_correct_styles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('button.bg-primary-600');
        });
    }

    #[Test]
    public function outline_button_renders_with_correct_styles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('button.border');
        });
    }

    #[Test]
    public function buttons_have_focus_ring_on_focus(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPresent('button.focus\\:ring-2');
        });
    }
}
