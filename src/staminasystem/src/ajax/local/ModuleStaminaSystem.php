<?php

namespace Lotgd\Ajax\Local;

use Jaxon\Response\Response;
use Lotgd\Core\AjaxAbstract;
use Tracy\Debugger;

require_once 'modules/staminasystem/lib/lib.php';

class ModuleStaminaSystem extends AjaxAbstract
{
    const TEXT_DOMAIN = 'module_staminasystem';

    public function show(): Response
    {
        $check = $this->checkLoggedInRedirect();

        if (true !== $check)
        {
            return $check;
        }

        $response = new Response();

        try
        {
            $stamina    = get_module_pref('stamina', 'staminasystem');
            $daystamina = 2000000;
            $redpoint   = get_module_pref('red', 'staminasystem');
            $amberpoint = get_module_pref('amber', 'staminasystem');
            $redPct     = get_stamina(0);
            $amberPct   = get_stamina(1);
            $greenPct   = get_stamina(2);

            $color = 'red';

            if ($greenPct > 0)
            {
                $color = 'green';
            }
            elseif ($amberPct > 0)
            {
                $color = 'orange';
            }

            $params = [
                'textDomain'     => $this->getTextDomain(),
                'currentStamina' => $stamina,
                'totalStamina'   => $daystamina,
                'amberPoint'     => $amberpoint,
                'redPoint'       => $redpoint,
                'barColor'       => $color,
            ];

            $act = get_player_action_list();

            $row = [];

            foreach ($act as $key => $value)
            {
                $keyT                               = \LotgdTranslator::t($key, [], $this->getTextDomain());
                $class                              = \LotgdTranslator::t('' != $value['class'] ? $value['class'] : 'Other', [], $this->getTextDomain());
                $row[$class][$keyT]                 = $value;
                $row[$class][$keyT]['levelinfo']    = stamina_level_up($key);
                $row[$class][$keyT]['costwithbuff'] = stamina_calculate_buffed_cost($key);
            }

            \ksort($row);

            foreach ($row as &$value)
            {
                \ksort($value);
            }

            $params['actions']  = $row;
            $params['buffList'] = \unserialize(get_module_pref('buffs', 'staminasystem'));

            // Dialog content
            $content = \LotgdTheme::render('@module/staminasystem/run/show.twig', $params);

            // Dialog title
            $title = \LotgdTranslator::t('title.show', [], $this->getTextDomain());

            // The dialog buttons
            $buttons = [
                [
                    'title' => \LotgdTranslator::t('modal.buttons.cancel', [], 'app_default'),
                    'class' => 'ui red deny button',
                ],
            ];

            //-- Options
            $options = [
                'autofocus'  => false,
                'classModal' => 'overlay fullscreen',
            ];

            $response->dialog->show($title, ['content' => $content, 'isScrollable' => true], $buttons, $options);
            $response->jQuery('#module-staminasystem-show')->removeClass('loading disabled');
            $response->jQuery('.ui.lotgd.tabular.menu .item')->tab();
            $response->jQuery('.ui.lotgd.progress')->progress(['precision' => 10]);
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            $response->dialog->error(\LotgdTranslator::t('jaxon.fail.request', [], 'app_default'));
        }

        return $response;
    }

    /**
     * Get text domain.
     */
    public function getTextDomain(): string
    {
        return self::TEXT_DOMAIN;
    }
}
