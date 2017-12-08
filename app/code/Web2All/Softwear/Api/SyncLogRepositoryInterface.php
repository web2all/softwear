<?php
namespace Web2All\Softwear\Api;

use Web2All\Softwear\Api\Data\SyncLogInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SyncLogRepositoryInterface 
{
    public function save(SyncLogInterface $synclog);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(SyncLogInterface $synclog);

    public function deleteById($id);
}
