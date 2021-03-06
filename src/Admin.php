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

use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\PageRegister;
use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\Pages\MenuPage;
use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\Pages\PageInterface;
use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\Pages\SubmenuPage;
use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\Section;
use TypistTech\Sunny\Vendor\TypistTech\WPBetterSettings\SettingRegister;
use TypistTech\Sunny\Vendor\TypistTech\WPContainedHook\Action;

/**
 * Final class Admin.
 *
 * The admin-specific functionality of the plugin.
 */
final class Admin implements LoadableInterface
{
    /**
     * Options store.
     *
     * @var OptionStore
     */
    private $optionStore;

    /**
     * Menu and submenu pages.
     *
     * @var (MenuPage|SubmenuPage)[]
     */
    private $pages = [];

    /**
     * Sections
     *
     * @var Section[]
     */
    private $sections = [];

    /**
     * Admin constructor.
     *
     * @param OptionStore $optionStore The Sunny option store.
     */
    public function __construct(OptionStore $optionStore)
    {
        $this->optionStore = $optionStore;
    }

    /**
     * {@inheritdoc}
     */
    public static function getHooks(): array
    {
        return [
            new Action('admin_menu', __CLASS__, 'registerPages'),
            new Action('admin_init', __CLASS__, 'registerSettings'),
        ];
    }

    /**
     * Menu slugs getter.
     *
     * @return string[]
     */
    public function getMenuSlugs(): array
    {
        return array_map(function (PageInterface $page) {
            return $page->getMenuSlug();
        }, $this->getPages());
    }

    /**
     * Page getter.
     *
     * @return (MenuPage|SubmenuPage)[]
     */
    private function getPages(): array
    {
        if (empty($this->pages)) {
            $sunnyPages = apply_filters('sunny_pages', []);

            $typedPages = array_filter($sunnyPages, function ($page) {
                return $page instanceof MenuPage || $page instanceof SubmenuPage;
            });
            $this->pages = array_values($typedPages);
        }

        return $this->pages;
    }

    /**
     * Add menus and submenus.
     *
     * @return void
     */
    public function registerPages()
    {
        $pageRegister = new PageRegister(
            $this->getPages()
        );
        $pageRegister->run();
    }

    /**
     * Register Sunny settings.
     *
     * @return void
     */
    public function registerSettings()
    {
        $settingRegister = new SettingRegister(
            $this->optionStore,
            ...$this->getSections()
        );
        $settingRegister->run();
    }

    /**
     * Section getter.
     *
     * @return Section[]
     */
    private function getSections(): array
    {
        if (empty($this->sections)) {
            $sunnySettingsSections = apply_filters('sunny_settings_sections', []);

            $typedSections = array_filter($sunnySettingsSections, function ($section) {
                return $section instanceof Section;
            });
            $this->sections = array_values($typedSections);
        }

        return $this->sections;
    }
}
