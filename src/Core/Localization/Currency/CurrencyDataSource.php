<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\PrestaShop\Core\Localization\Currency;

use PrestaShop\PrestaShop\Core\Localization\Currency\DataLayer\CurrencyInstalled as CurrencyInstalledDataLayer;

/**
 * Localization CurrencyData source
 * Uses a stack of middleware data layers to read / write CurrencyData objects
 */
class CurrencyDataSource implements DataSourceInterface
{
    /**
     * The top layer of the middleware stack
     *
     * @var CurrencyDataLayerInterface
     */
    protected $topLayer;

    /**
     * @var CurrencyInstalledDataLayer
     */
    protected $installedDataLayer;

    /**
     * CurrencyDataSource constructor needs CurrencyDataLayer objects.
     * This top layer might be chained with lower layers and will be the entry point of this middleware stack.
     *
     * @param CurrencyDataLayerInterface $topLayer
     * @param CurrencyInstalledDataLayer $installedDataLayer
     */
    public function __construct(CurrencyDataLayerInterface $topLayer, CurrencyInstalledDataLayer $installedDataLayer)
    {
        $this->topLayer           = $topLayer;
        $this->installedDataLayer = $installedDataLayer;
    }

    /**
     * Get complete currency data by currency code, in a given language
     *
     * @param LocalizedCurrencyId $localizedCurrencyId
     *  The currency data identifier (currency code + locale code)
     *
     * @return CurrencyData
     *  The currency data
     */
    public function getLocalizedCurrencyData(LocalizedCurrencyId $localizedCurrencyId)
    {
        return $this->topLayer->read($localizedCurrencyId);
    }

    /**
     * Is this currency available ?
     * (an available currency is not deleted AND is active)
     *
     * @param $currencyCode
     *
     * @return bool
     *  True if currency is available
     */
    public function isCurrencyAvailable($currencyCode)
    {
        return $this->installedDataLayer->isAvailable($currencyCode);
    }

    /**
     * Get all the available (installed + active) currencies' data
     *
     * @param string $localeCode
     *  IETF tag. Data will be translated in this language
     *
     * @return CurrencyData[]
     *  The available currencies' data
     */
    public function getAvailableCurrenciesData($localeCode)
    {
        $currencyCodes  = $this->installedDataLayer->getAvailableCurrencyCodes();
        $currenciesData = [];
        foreach ($currencyCodes as $currencyCode) {
            $currenciesData[] = $this->getLocalizedCurrencyData(new LocalizedCurrencyId($currencyCode, $localeCode));
        }

        return $currenciesData;
    }
}
