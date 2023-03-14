<?php

namespace App\Actions;

use App\Enums\SignOnState;
use Codedungeon\PHPCliColors\Color;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use NunoMaduro\LaravelConsoleMenu\Menu;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;
use function Termwind\{ask}; //@codingStandardsIgnoreLine
use function Termwind\{render}; //@codingStandardsIgnoreLine
use function Termwind\{terminal}; //@codingStandardsIgnoreLine

class DisplayLogonMenu
{
    use AsAction;
    use WithAttributes;

    public function handle(SignOnState $state): array
    {
        $this->set('state', $state);
        
        if ($state !== SignOnState::INVALID) {
            collect()->times(terminal()->height(), fn () =>  render(PHP_EOL));
            $this->displaySplashScreen();
        }

        return $this->menu()->open();
    }

    private function menu(): Menu
    {
        return with(new Menu('Sign on'), function (Menu $menu) {
            if ($this->state === SignOnState::INVALID) {
                //@codingStandardsIgnoreLine
                $menu->setTitle("Sign On\n\n".Color::BG_LIGHT_RED.'The username or password you entered is incorrect!'.Color::BG_BLUE);
            }

            return $menu
                ->addOption(['guest', null], 'Guest')
                ->addItem('New User', $this->credentialsMenu($menu))
                ->disableDefaultItems();
        });
    }

    private function credentialsMenu(Menu $menu): callable
    {
        return function (CliMenu $cliMenu) use ($menu) {
            $style = ((new MenuStyle($cliMenu->getTerminal()))->setBg('245')->setFg('white'));

            $username = $cliMenu
                ->askText($style)
                ->setPromptText('Username')
                ->setPlaceholderText('New User')
                ->setValidator(fn ($username) => $username !== 'New User' && strlen($username) > 2)
                ->ask()
                ->fetch();

            $password = $cliMenu
                ->askPassword($style)
                ->setPromptText('Password')
                ->setPlaceholderText('password')
                ->setValidator(function ($password) {
                    return strlen($password) > 4 && $password !== 'password';
                })
                ->ask()
                ->fetch();

            $menu->setResult([$username, $password]);
            $cliMenu->close();
        };
    }

    private function displaySplashScreen(): void
    {
        //@codingStandardsIgnoreStart
        render(<<<'HTML'
                <div class="py-1 ml-2">
                    <div class="px-1 bg-blue-300 text-black">🖥 &nbsp;CLI Edition</div><br>
                    <em class="mt-2 text-blue-200">
                        <br><br>
                        ██████╗&nbsp;██████╗&nbsp;&nbsp;██████╗&nbsp;██╗<br>
                        ██╔══██╗╚════██╗██╔═══██╗██║&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                        ██████╔╝&nbsp;█████╔╝██║&nbsp;&nbsp;&nbsp;██║██║&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                        ██╔═══╝&nbsp;&nbsp;╚═══██╗██║&nbsp;&nbsp;&nbsp;██║██║&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                        ██║&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;██████╔╝╚██████╔╝███████╗<br>
                    </em>
                </div>
            HTML);
        //@codingStandardsIgnoreEnd

        ask('<div class="px-1 bg-blue-300 text-black">Press enter to continue.</div>');
    }
}
