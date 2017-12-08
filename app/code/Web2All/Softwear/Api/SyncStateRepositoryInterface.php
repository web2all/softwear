<?php
namespace Web2All\Softwear\Api;

use Web2All\Softwear\Api\Data\SyncStateInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SyncStateRepositoryInterface 
{
    public function save(SyncStateInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(SyncStateInterface $page);

    public function deleteById($id);
}
