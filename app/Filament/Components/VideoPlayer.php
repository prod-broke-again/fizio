<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\HtmlString;

/**
 * Компонент для отображения видео в Filament
 */
class VideoPlayer extends Field
{
    protected string $view = 'filament.components.video-player';
    
    protected string $viewIdentifier = 'video-player';
    
    /**
     * Максимальная ширина видео
     */
    public function maxWidth(string $width): static
    {
        $this->extraAttributes(['style' => "max-width: {$width}"]);
        
        return $this;
    }
    
    /**
     * Максимальная высота видео
     */
    public function maxHeight(string $height): static
    {
        $this->extraAttributes(['style' => "max-height: {$height}"]);
        
        return $this;
    }
    
    /**
     * Автовоспроизведение
     */
    public function autoplay(bool $autoplay = true): static
    {
        $this->extraAttributes(['autoplay' => $autoplay]);
        
        return $this;
    }
    
    /**
     * Зацикливание
     */
    public function loop(bool $loop = true): static
    {
        $this->extraAttributes(['loop' => $loop]);
        
        return $this;
    }
    
    /**
     * Управление
     */
    public function controls(bool $controls = true): static
    {
        $this->extraAttributes(['controls' => $controls]);
        
        return $this;
    }
    
    /**
     * Без звука
     */
    public function muted(bool $muted = true): static
    {
        $this->extraAttributes(['muted' => $muted]);
        
        return $this;
    }
}
