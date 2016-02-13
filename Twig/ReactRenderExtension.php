<?php

namespace Limenius\ReactBundle\Twig;

use Nacmartin\PhpExecJs\PhpExecJs;
use AppBundle\Exception\EvalJsError;
use Limenius\ReactBundle\Renderer\ReactRenderer;

class ReactRenderExtension extends \Twig_Extension
{
    private $renderer;
    protected $renderServerSide = false;
    protected $renderClientSide = false;

    /**
     * Constructor
     * 
     * @param ReactRenderer $renderer 
     * @param string $defaultRendering 
     * @param boolean $trace 
     * @access public
     * @return void
     */
    public function __construct(ReactRenderer $renderer, $defaultRendering, $trace = false)
    {
        $this->renderer = $renderer;
        $this->trace = $trace;

        switch ($defaultRendering) {
        case 'only_serverside':
            $this->renderClientSide = false;
            $this->renderServerSide = true;
            break;
        case 'only_clientside':
            $this->renderClientSide = true;
            $this->renderServerSide = false;
            break;
        case 'both':
            $this->renderClientSide = true;
            $this->renderServerSide = true;
            break;
        }
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('react_component', array($this, 'reactRenderComponent'), array('is_safe' => array('html'))));
    }

    public function reactRenderComponent($componentName, $options = array())
    {
        $uuid = 'sfreact-'.uniqid();
        $propsString = isset($options['props']) ? $options['props'] : '';
        $str = '';
        $trace = $this->shouldTrace($options);
        if ($this->shouldRenderClientSide($options)) {
            $str .= '<div class="js-react-on-rails-component" style="display:none" data-component-name="'.$componentName.'" data-props="'.htmlspecialchars($propsString).'" data-trace="'.($trace ? 'true' : 'false').'" data-dom-id="'.$uuid.'"></div>';
        }
        $str .= '<div id="'.$uuid.'">';
        if ($this->shouldRenderServerSide($options)) {

            $serverSideStr = $this->renderer->render($componentName, $propsString, $uuid, $trace);
            $str .= $serverSideStr;
        }
        $str .= '</div>';
        return $str;
    }

    public function shouldRenderServerSide($options) {
        if (isset($options['rendering'])) {
            if (in_array($options['rendering'], ['server_side', 'both'])) {
                return true;
            } else {
                return false;
            }
        }
        return $this->renderServerSide;
    }

    protected function shouldTrace($options) {
        return (isset($options['trace']) ? $options['trace'] : $this->trace);
    }

    public function shouldRenderClientSide($options) {
        if (isset($options['rendering'])) {
            if (in_array($options['rendering'], ['client_side', 'both'])) {
                return true;
            } else {
                return false;
            }
        }
        return $this->renderClientSide;
    }

    public function getName()
    {
        return 'react_render_extension';
    }
}
