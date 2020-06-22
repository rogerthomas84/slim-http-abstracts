<?php
/** @noinspection PhpUndefinedClassInspection */

namespace SlimHttpAbstracts\ControllerAbstracts;

use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Skinny\Auth;
use Slim\Exception\MethodNotAllowedException;
use Slim\Psr7\Response;
use Slim\Views\PhpRenderer;

/**
 * Class HtmlCoreControllerAbstract
 * @package SlimHttpAbstracts\ControllerAbstracts
 */
class HtmlCoreControllerAbstract extends CoreControllerAbstract
{
    /**
     * The path to the view folder
     *
     * @var string|null
     */
    protected $viewPath = null;

    /**
     * The layout file for the template.
     *
     * @var string|null
     */
    protected $layoutFile = null;

    /**
     * Get the view renderer.
     *
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        return $this->getContainerItem('renderer');
    }

    /**
     * Render a specific view script.
     *
     * @param string $fileName
     * @param array $args
     * @return Response|ResponseInterface
     */
    protected function render($fileName, array $args=[])
    {
        if ($this->viewPath !== null) {
            $this->getContainer()->set('renderer', function (Container $c) {
                $renderer = new PhpRenderer($this->viewPath);
                if ($this->layoutFile !== null) {
                    $renderer->setLayout($this->layoutFile);
                }
                return $renderer;
            });
        }

        if ($this->container->has('renderer') === false) {
            $this->getLogger()->addError('No viewPath has been assigned');
            return $this->getResponse()->withStatus(500);
        }

        $content = $this->getRenderer()->render(
            $this->getResponse(),
            $fileName,
            $args
        );
        /* @var $content Response|ResponseInterface */
        return $content;
    }

    /**
     * Is the user logged in?
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        return Auth::getInstance()->isLoggedIn();
    }
}
