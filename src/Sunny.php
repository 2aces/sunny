<?php
/**
 * Sunny
 *
 * Automatically purge CloudFlare cache, including cache everything rules.
 *
 * @package   Sunny
 *
 * @author    Typist Tech <sunny@typist.tech>
 * @copyright 2017 Typist Tech
 * @license   GPL-2.0+
 *
 * @see       https://www.typist.tech/projects/sunny
 * @see       https://wordpress.org/plugins/sunny/
 */

declare(strict_types=1);

namespace TypistTech\Sunny;

use TypistTech\Sunny\Ads\I18nPromoter;
use TypistTech\Sunny\Ads\ReviewNotice;
use TypistTech\Sunny\Cloudflare\Admin as CloudflareAdmin;
use TypistTech\Sunny\Vendor\TypistTech\WPContainedHook\Action;
use TypistTech\Sunny\Vendor\TypistTech\WPContainedHook\Loader;

/**
 * Final class Sunny
 *
 * The core plugin class.
 */
final class Sunny implements LoadableInterface
{
    /**
     * The dependency injection container.
     *
     * @var Container
     */
    private $container;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Loader Maintains and registers all hooks for the plugin.
     */
    private $loader;

    /**
     * Sunny constructor.
     */
    public function __construct()
    {
        $this->container = new Container;
        $this->loader = new Loader($this->container);

        $this->container->initialize();

        $loadables = [
            __CLASS__,
            Admin::class,
            CloudflareAdmin::class,
            I18n::class,
            I18nPromoter::class,
            ReviewNotice::class,
        ];

        foreach ($loadables as $loadable) {
            /* @var LoadableInterface $loadable */
            $this->loader->add(...$loadable::getHooks());
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getHooks(): array
    {
        return [
            new Action(__CLASS__, 'plugin_loaded', 'giveContainer', 5),
        ];
    }

    /**
     * Expose Container via WordPress action.
     *
     * @return void
     */
    public function giveContainer()
    {
        do_action('sunny_get_container', $this->container);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @return void
     */
    public function run()
    {
        $this->loader->run();
    }
}
