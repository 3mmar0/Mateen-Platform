<?php
namespace App\Enums;
enum Role:string { case Admin='admin'; case Support='support'; case Supervisor='supervisor'; case Teacher='teacher'; case Student='student'; case Mateen='mateen';
    public function isStaff(): bool { return in_array($this, [self::Admin,self::Support,self::Supervisor,self::Teacher], true); }
}
