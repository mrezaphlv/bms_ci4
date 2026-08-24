<?php

namespace App\Models;

use CodeIgniter\Model;

class MPajak extends Model
{
    protected $DBGroup = 'default';

    public function droplistpph(): array
    {
        return $this->db
            ->query(
                "select * from m_pajak where flag_id = true and lower(nama_pajak) like 'pph%'"
            )
            ->getResult();
    }

    public function droplistppn(): array
    {
        return $this->db
            ->query(
                "select * from m_pajak where flag_id = true and lower(nama_pajak) like 'ppn%'"
            )
            ->getResult();
    }

    public function droplist_pph(): array
    {
        return $this->db
            ->query(
                "select * from m_pajak where flag_id = true and lower(nama_pajak) like 'pph%'"
            )
            ->getResult();
    }

    public function getrow($id): ?object
    {
        return $this->db
            ->query(
                "select * from m_pajak where flag_id = true and id = ?",
                [$id]
            )
            ->getRow();
    }

    public function droplist(): array
    {
        return $this->db
            ->query(
                "select * from m_pajak where flag_id = true and lower(nama_pajak) like 'ppn%'"
            )
            ->getResult();
    }
}
