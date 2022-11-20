use std::collections::HashMap;

use ext_php_rs::args::Arg;
use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::builders::FunctionBuilder;
use ext_php_rs::class::ClassMetadata;
use ext_php_rs::class::ConstructorMeta;
use ext_php_rs::class::ConstructorResult;
use ext_php_rs::class::RegisteredClass;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::flags::DataType;
use ext_php_rs::flags::MethodFlags;
use ext_php_rs::props::Property;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ExecuteData;

use crate::components::ds::exceptions::UNDERFLOW_EXCEPTION_CE;
use crate::components::ds::interfaces::QUEUE_INTERFACE_CE;

const QUEUE_IMPLMENTATION_CLASS_NAME: &str = "Psl\\DataStructure\\Queue";

static QUEUE_IMPLMENTATION_METADATA: ClassMetadata<Queue> = ClassMetadata::new();

pub fn build() {
    let builder = ClassBuilder::new(QUEUE_IMPLMENTATION_CLASS_NAME)
        .method(
            FunctionBuilder::new("enqueue", Queue::enqueue)
                .arg(Arg::new("node", DataType::Mixed))
                .returns(DataType::Void, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("peek", Queue::peek)
                .returns(DataType::Mixed, false, true)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("pull", Queue::pull)
                .returns(DataType::Mixed, false, true)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("dequeue", Queue::dequeue)
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("count", Queue::count)
                .returns(DataType::Long, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .flags(ClassFlags::Final | ClassFlags::NoDynamicProperties)
        .implements(unsafe { QUEUE_INTERFACE_CE.unwrap() })
        .object_override::<Queue>();

    let class = builder
        .build()
        .unwrap_or_else(|_| panic!("Unable to build class `{}`", QUEUE_IMPLMENTATION_CLASS_NAME));

    QUEUE_IMPLMENTATION_METADATA.set_ce(class);
}

pub struct Queue {
    queue: Vec<Zval>,
}

impl Queue {
    pub fn construct(ex: &mut ExecuteData) -> ConstructorResult<Self> {
        if ex.parser().parse().is_err() {
            return ConstructorResult::ArgError;
        }

        Self { queue: vec![] }.into()
    }

    pub extern "C" fn enqueue(ex: &mut ExecuteData, retval: &mut Zval) {
        let mut node = Arg::new("node", DataType::Mixed);
        let this = parse_method!(ex, node);

        let node: &Zval = parse_zval_argument!(node, "node");

        this.queue.push(node.shallow_clone());

        retval.set_null();
    }

    pub extern "C" fn peek(ex: &mut ExecuteData, retval: &mut Zval) {
        let this = parse_method!(ex);

        if let Some(value) = this.queue.first().map(|z| z.shallow_clone()) {
            *retval = value;
        } else {
            retval.set_null();
        }
    }

    pub extern "C" fn pull(ex: &mut ExecuteData, retval: &mut Zval) {
        let this = parse_method!(ex);

        if !this.queue.is_empty() {
            *retval = this.queue.remove(0);
        } else {
            retval.set_null();
        }
    }

    pub extern "C" fn dequeue(ex: &mut ExecuteData, retval: &mut Zval) {
        let this = parse_method!(ex);

        if !this.queue.is_empty() {
            *retval = this.queue.remove(0);
        } else {
            let ce = unsafe { UNDERFLOW_EXCEPTION_CE.unwrap() };

            throw!(ce, "Cannot dequeue a node from an empty queue.");
        }
    }

    pub extern "C" fn count(ex: &mut ExecuteData, retval: &mut Zval) {
        let this = parse_method!(ex);

        retval.set_long(this.queue.len() as i32);
    }
}

impl RegisteredClass for Queue {
    const CLASS_NAME: &'static str = QUEUE_IMPLMENTATION_CLASS_NAME;

    const CONSTRUCTOR: Option<ConstructorMeta<Self>> = Some(ConstructorMeta {
        constructor: Self::construct,
        build_fn: |func: FunctionBuilder| -> FunctionBuilder { func },
    });

    fn get_metadata() -> &'static ClassMetadata<Self> {
        &QUEUE_IMPLMENTATION_METADATA
    }

    fn get_properties<'a>() -> HashMap<&'static str, Property<'a, Self>> {
        HashMap::new()
    }
}

implement_class!(Queue, QUEUE_IMPLMENTATION_CLASS_NAME);
