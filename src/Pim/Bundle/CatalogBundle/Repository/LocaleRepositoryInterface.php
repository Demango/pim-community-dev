<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Locale repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocaleRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Return an array of activated locales
     *
     * @return LocaleInterface[]
     */
    public function getActivatedLocales();

    /**
     * Return an array of activated locales codes
     *
     * @return array
     */
    public function getActivatedLocaleCodes();

    /**
     * Return a query builder for activated locales
     *
     * @return mixed
     */
    public function getActivatedLocalesQB();

    /**
     * Return a query builder for all locales
     *
     * @return mixed
     */
    public function getLocalesQB();

    /**
     * @return mixed
     */
    public function createDatagridQueryBuilder();

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param ChannelInterface $channel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel);
}
