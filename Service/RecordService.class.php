<?php
namespace Record\Service;

use Record\Libs\Record;
use Record\Model\RecordModel;
use System\Service\BaseService;

class RecordService extends BaseService {
    static function createRrcord(Record $record) {
        //获取上一条合法的record id
        $where['status'] = RecordModel::STATUS_VAILD;
        $where['to'] = $record->getTo();
        $where['to_type'] = $record->getToType();
        $last_vaild_record = M($record->table_name)->where($where)->order('id desc')->find();
        $last_vaild_balance = self::getBalance($record)['data']; //获取最近的余额信息

        $data = [
            'parent_id' => $last_vaild_record ? $last_vaild_record['id'] : 0,
            'to' => $record->getTo(),
            'to_type' => $record->getToType(),
            'from' => $record->getFrom(),
            'from_type' => $record->getFromType(),
            'target' => $record->getTarget(),
            'target_type' => $record->getTargetType(),
            'income' => $record->getIncome(),
            'pay' => $record->getPay(),
            'balance' => ($last_vaild_balance + $record->getIncome() - $record->getPay()),//计算当前记录的余额
            'detail' => $record->getDetail(),
            'status' => $record->getStatus(),
            'create_time' => time(),
            'remark' => $record->getRemark()
        ];
        $result = M($record->table_name)->add($data);
        if ($result) {
            return self::createReturn(true, $result);
        }

        return self::createReturn(false, null, '操作失败');
    }

    static function getBalance(Record $record) {
        $where = [
            'to' => $record->getTo(),
            'to_type' => $record->getToType(),
            'status' => RecordModel::STATUS_VAILD
        ];
        $lists = M($record->table_name)->field('income,pay')->where($where)->select();
        $total = 0;
        for ($i = 0; $i < count($lists); $i++) {
            $total = $total + $lists[$i]['income'] - +$lists[$i]['pay'];
        }

        return self::createReturn(true, $total, 'ok');
    }
}