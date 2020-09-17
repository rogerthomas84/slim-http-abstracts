<?php
namespace SlimHttpAbstracts\Error\Renderers;

use Slim\Error\Renderers\HtmlErrorRenderer;

/**
 * Class HtmlAppErrorRenderer
 * @package SlimHttpAbstracts\Error\Renderers
 */
class AppHtmlErrorRenderer extends HtmlErrorRenderer
{
    /**
     * @var string
     */
    protected $defaultErrorTitle = 'Application';

    /**
     * @param string $title
     * @return $this
     */
    public function setDefaultErrorTitle(string $title)
    {
        $this->defaultErrorTitle = $title;
        return $this;
    }
}
