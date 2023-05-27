<?php
/**
 *  package: Joomill - Tag Manager
 *  copyright: Copyright (c) 2023. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 3 or later
 *  link: https://www.joomill.nl
 **/


// No direct access to this file
defined('_JEXEC') or die;

/**
 * Joomill - Tag Manager plugin
 *
 * @since   1.0.0
 */
class PlgSystemTagmanager extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, array $config = array())
	{
		parent::__construct($subject, $config);
	}

	/**
	 * onAfterRender trigger
	 *
	 * @return  void
	 * @since   3.0
	 */
	public function onAfterRender()
	{
		// Only for frontend
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// Load GTM code
		if ($this->params->get('gtm_id'))
		{
            $gtm_id = $this->params->get('gtm_id');

            $doc = JFactory::getDocument();
            $doc->addCustomTag('<link rel="preconnect" href="https://www.googletagmanager.com">');
            $doc->addCustomTag('<link rel="dns-prefetch" href="https://www.googletagmanager.com">');

            // Google Tag Manager - party loaded in head
            $headScript = "
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','" . $gtm_id . "');</script>
            <!-- End Google Tag Manager -->
            ";

            // Google Tag Manager - partly loaded directly after body
            $bodyScript = "
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=" . $gtm_id . "\"
            height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
            ";

            $buffer     = $this->app->getBody();
            $buffer     = str_replace("</head>", $headScript . "</head>", $buffer);
            $buffer     = preg_replace("/<body(\s[^>]*)?>/i", "<body\\1>\n" . $bodyScript, $buffer);
            $this->app->setBody($buffer);
        }

        // Load GA4 code
        if ($this->params->get('ga_id'))
        {
            $ga_id = $this->params->get('ga_id');

            $headScript = "
            <!-- Google tag (gtag.js) -->
              <script async src=\"https://www.googletagmanager.com/gtag/js?id=" . $ga_id . "\"></script>
              <script>
                    window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
            
                gtag('config', '$ga_id' );
              </script>
            ";

            $buffer     = $this->app->getBody();
            $buffer     = preg_replace("/<head(\s[^>]*)?>/i", "<head\\1>\n" . $headScript, $buffer);
            $this->app->setBody($buffer);
        }

        // Load Matomo code
        if (($this->params->get('matomo_url')) && ($this->params->get('matomo_id')))
        {
            $matomo_url = $this->params->get('matomo_url');
            $matomo_id = $this->params->get('matomo_id');

            $headScript = "
            <!-- Matomo -->
            <script>
              var _paq = window._paq = window._paq || [];
              /* tracker methods like \"setCustomDimension\" should be called before \"trackPageView\" */
              _paq.push(['trackPageView']);
              _paq.push(['enableLinkTracking']);
              (function() {
                var u=\"" . $matomo_url . "/\";
                _paq.push(['setTrackerUrl', u+'matomo.php']);
                _paq.push(['setSiteId', '" . $matomo_id . "']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
              })();
            </script>
            <noscript><p><img src=\"" . $matomo_url . "/matomo.php?idsite=" . $matomo_id . "&amp;rec=1\" style=\"border:0;\" alt=\"\" /></p></noscript>
            <!-- End Matomo Code -->
            ";

            $buffer     = $this->app->getBody();
            $buffer     = str_replace("</head>", $headScript . "</head>", $buffer);
            $this->app->setBody($buffer);
        }
	}
}
