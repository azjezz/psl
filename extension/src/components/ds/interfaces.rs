use ext_php_rs::args::Arg;
use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::builders::FunctionBuilder;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::flags::DataType;
use ext_php_rs::flags::MethodFlags;
use ext_php_rs::zend::ce;
use ext_php_rs::zend::ClassEntry;

pub static mut QUEUE_INTERFACE_CE: Option<&'static ClassEntry> = None;
pub static mut PRIORITY_QUEUE_INTERFACE_CE: Option<&'static ClassEntry> = None;
pub static mut STACK_INTERFACE_CE: Option<&'static ClassEntry> = None;

pub fn build() {
    let queue_interface_ce = ClassBuilder::new("Psl\\DataStructure\\QueueInterface")
        .flags(ClassFlags::Interface)
        .implements(ce::countable())
        .method(
            FunctionBuilder::new_abstract("enqueue")
                .arg(Arg::new("node", DataType::Mixed))
                .returns(DataType::Void, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("peek")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("pull")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("dequeue")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("count")
                .returns(DataType::Long, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .build()
        .unwrap();

    let priority_queue_interface_ce =
        ClassBuilder::new("Psl\\DataStructure\\PriorityQueueInterface")
            .flags(ClassFlags::Interface)
            .implements(ce::countable())
            .implements(queue_interface_ce)
            .method(
                FunctionBuilder::new_abstract("enqueue")
                    .arg(Arg::new("node", DataType::Mixed))
                    .not_required()
                    .arg(Arg::new("priority", DataType::Long).default("0".to_string()))
                    .returns(DataType::Void, false, false)
                    .build()
                    .unwrap(),
                MethodFlags::Public | MethodFlags::StrictTypes,
            )
            .build()
            .unwrap();

    let stack_interface_ce = ClassBuilder::new("Psl\\DataStructure\\StackInterface")
        .flags(ClassFlags::Interface)
        .implements(ce::countable())
        .method(
            FunctionBuilder::new_abstract("push")
                .arg(Arg::new("item", DataType::Mixed))
                .returns(DataType::Void, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("peek")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("pull")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("pop")
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .method(
            FunctionBuilder::new_abstract("count")
                .returns(DataType::Long, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::StrictTypes,
        )
        .build()
        .unwrap();

    unsafe {
        QUEUE_INTERFACE_CE.replace(queue_interface_ce);
        PRIORITY_QUEUE_INTERFACE_CE.replace(priority_queue_interface_ce);
        STACK_INTERFACE_CE.replace(stack_interface_ce);
    }
}
