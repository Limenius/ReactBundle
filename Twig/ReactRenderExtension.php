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
    protected $registeredStores = array();

    /**
     * Constructor
     * 
     * @param ReactRenderer $renderer 
     * @param string $defaultRendering 
     * @param boolean $trace 
     * @access public
     * @return ReactRenderExtension
     */
    public function __construct(ReactRenderer $renderer, $defaultRendering, $trace = false)
    {
        $this->renderer = $renderer;
        $this->trace = $trace;

        switch ($defaultRendering) {
        case 'server_side':
            $this->renderClientSide = false;
            $this->renderServerSide = true;
            break;
        case 'client_side':
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
            new \Twig_SimpleFunction('react_component', array($this, 'reactRenderComponent'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('redux_store', array($this, 'reactReduxStore'), array('is_safe' => array('html')))
        );
    }

    public function reactRenderComponent($componentName, $options = array())
    {
        $uuid = 'sfreact-'.uniqid();
        $props = isset($options['props']) ? $options['props'] : '{}';
        $propsString = is_array($props) ? json_encode($props) : $props;

        $str = '';
        $trace = $this->shouldTrace($options);
        if ($this->shouldRenderClientSide($options)) {
            $str .=  sprintf(
                '<div class="js-react-on-rails-component" style="display:none" data-component-name="%s" data-props="%s" data-trace="%s" data-dom-id="%s"></div>',
                $componentName, htmlspecialchars($propsString), var_export($trace, true), $uuid
            );
        }
        $str .= '<div id="'.$uuid.'">';
        if ($this->shouldRenderServerSide($options)) {
            $serverSideStr = $this->renderer->render($componentName, $propsString, $uuid, $this->registeredStores, $trace);
            $str .= $serverSideStr;
        }
        $str .= '</div>';
        return $str;
    }

    public function reactReduxStore($storeName, $props)
    {
        $propsString = is_array($props) ? json_encode($props) : $props;
        $this->registeredStores[$storeName] = $propsString;
        return sprintf(
            '<div class="js-react-on-rails-store" style="display:none" data-store-name="%s" data-props="%s"></div>',
            $storeName, htmlspecialchars($propsString)
        );
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
