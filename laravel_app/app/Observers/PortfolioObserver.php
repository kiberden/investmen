<?php

namespace App\Observers;

use App\Models\Portfolio;

class PortfolioObserver
{
    /**
     * Автоматически обновляет updated_at при обновлении модели.
     */
    public function updating(Portfolio $portfolio): void
    {
        $portfolio->updated_at = now();
    }
}
