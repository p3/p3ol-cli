<?php

//@codingStandardsIgnoreStart

namespace Tests;

enum TestPacket: string
{
    case INIT_ACK_PACKET = '5ab71100037f7f240d';
    case AB_PACKET = '5a4ea900122d1c204142477565737432000000000068690d';
    case cQ_PACKET = '5ac20800197f7fa0635100200001000107040000000403010276620002000d';
    case CJ_AT_PACKET = '5a1cc200b0161220415400180001000109032000620f13020102010a010101000a06300964656164656e640202010201020001000a06370957656c636f6d650202010201020001001006300954686520382d626974204775790202010201020001000e06300954656368204c696e6b6564020201020102000100110630094e6f7374616c676961204e657264020201020102000100070630094e65777302020102010200011100011d0000070101000701020012000d5a3bed00031612240d';
    case Dd_AT_PACKET = '5ab10a02eb101020415400110001000d040002320101001303200128010901010c010500000000000c000500000000a1011d000109013501000101102401011003010010040100100c0320001e1008015b10400105103a0320001e01000108190000101702371c100b010110270100010200011100010a01010114263c68313e57656c636f6d6520746f2052652d414f4c205b414c504841204f4e455d3c2f68313e01146f3c623e446973636c61696d65723c2f623e3a20576520617265206e6f742073656c6c696e6720616363657373206f722073656c6c696e6720612070726f647563743b20627574207261746865722075736572732061726520706c656467696e6720746f20737570706f72742074686501145f2070726f6a65637420616e6420696e2065786368616e676520776527726520726577617264696e67207468656d206279206c657474696e67207468656d207472792052452d414f4c20696e206561726c7920616c706861206163636573732e01142e3c62723e3c62723e596f752068617665206c6f6767656420696e2061732061204775657374213c62723e3c62723e01142a3c623e52652d414f4c3c2f623e20697320616e20414f4cae2073657276657220656d756c61746f72202d01145320776869636820747269657320746f2070726f7669646520616e20657870657269656e636520746861742077617320617661696c61626c6520647572696e6720746865203139393073272e3c62723e3c62723e011457417320612047756573742c20796f757220657870657269656e63652077696c6c206265206c696d6974656420746f206368617420726f6f6d7320616e64206d65737361676520626f617264732c20686f77657665722c2001145d796f752077696c6c206e6f7420626520616c6c6f77656420746f20706f7374206d6573736167657320746f2074686520626f617264732e2042757420796f752063616e20667265656c79206368617420696e20726f6f6d73213c62723e011d000111000010000002000d5a6d29020b1110204154001200010001090320001e010a010101144d3c62723e4b65657020696e206d696e642074686174207468652073657276657220697320696e20616e203c623e414c5048413c2f623e207374617465206f6620646576656c6f706d656e742c2001144d7768696368206d65616e73206e6f742065766572797468696e6720697320676f696e6720746f20776f726b20617320796f75206d61792065787065637420697420746f6f2e3c62723e3c62723e01144a446576656c6f706d656e74206973203c753e6f6e2d676f696e6720616e6420636f6e74696e756f75733c2f753e2c2069742074616b65732074696d6520746f2072657365617263682c200114276c6561726e2c20746573742c20616e6420696d706c656d656e7420746865206368616e67657320011439736f20706c6561736520686176652070617469656e747320616e6420656e6a6f79207768617420646f657320776f726b213c62723e3c62723e01143d53657276657220646576656c6f706d656e74206973203c753e6f6e2d676f696e6720616e6420636f6e74696e756f75732e3c2f753e3c62723e3c62723e0114553c623e52652d414f4c20697320696e206e6f2077617920616666696c6961746564207769746820416d6572696361204f6e6c696e65206f7220616e79206f6620697473207375627369646961726965732e3c2f623e011d000111000010000002000d5a0c680012121020415400140001000e0c0253430012000d';
    case Dd_INVALID_AT_PACKET = '5a1e910042101020415400110001000d113254686520757365726e616d65206f722070617373776f726420796f757f656e746572656420697320696e636f7272656374210002000d';
    case SC_AT_PACKET = '5a026f003d131120415400150001000d1a0001090320001e10180957656c636f6d652c200d1d000c0701010c46000c090101000a020114011401210c0601010111000d';
}
