<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('count_bets', [$this, 'formatCount']),
        ];
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('time_left', [$this, 'formatLeftTime']),
        ];
    }
    
    public function formatCount(int $count)
    {        
        if (in_array($count % 100, range(10, 19))) {
            $suffix = ' ставок';
        } else {
            switch ($count % 10) {
                case 1:
                    $suffix = ' ставка';
                    break;
                case 2:
                case 3:
                case 4:
                    $suffix = ' ставки';
                    break;
                default:
                    $suffix = ' ставок';
            }
        }
        
        return (string)$count . $suffix;
    }
    
    public function formatLeftTime(\DateTime $dateTime): array
    {
        $result = ['time' => '', 'class' => false];
        
        $diff = (new \DateTime())->diff($dateTime);

        if ($diff->invert === 1) {
            return $result;
        }
        
        if ($diff->d === 0) {
            $result['time'] = $diff->format('%H:%i:%s');
            if ($diff->h === 0) {
                $result['class'] = true;
            }
            return $result;
        }
        
        if (in_array($diff->d % 100, range(11, 20))) {
            $result['time'] = (string)$diff->d . ' дней';
            return $result;
        }
        
        switch ($diff->d % 10) {
            case 1:
                $result['time'] = (string)$diff->d . ' день';
                break;
            case 2:
            case 3:
            case 4:
                $result['time'] = (string)$diff->d . ' дня';
                break;
            default:
                $result['time'] = (string)$diff->d . ' дней';
        }
        
        return $result;
    }
}
