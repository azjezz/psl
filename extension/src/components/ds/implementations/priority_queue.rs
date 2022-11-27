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
use ext_php_rs::types::ZendClassObject;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ExecuteData;

use crate::components::ds::exceptions::UNDERFLOW_EXCEPTION_CE;
use crate::components::ds::interfaces::PRIORITY_QUEUE_INTERFACE_CE;

const PRIORITY_QUEUE_IMPLMENTATION_CLASS_NAME: &str = "Psl\\DataStructure\\PriorityQueue";

static PRIORITY_QUEUE_IMPLMENTATION_METADATA: ClassMetadata<PriorityQueue> = ClassMetadata::new();

pub fn build() {
    let builder = ClassBuilder::new(PRIORITY_QUEUE_IMPLMENTATION_CLASS_NAME)
        .method(
            FunctionBuilder::new("enqueue", PriorityQueue::enqueue)
                .arg(Arg::new("node", DataType::Mixed))
                .not_required()
                .arg(Arg::new("priority", DataType::Long).default("0".to_string()))
                .returns(DataType::Void, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("peek", PriorityQueue::peek)
                .returns(DataType::Mixed, false, true)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("pull", PriorityQueue::pull)
                .returns(DataType::Mixed, false, true)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("dequeue", PriorityQueue::dequeue)
                .returns(DataType::Mixed, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .method(
            FunctionBuilder::new("count", PriorityQueue::count)
                .returns(DataType::Long, false, false)
                .build()
                .unwrap(),
            MethodFlags::Public,
        )
        .flags(ClassFlags::Final | ClassFlags::NoDynamicProperties)
        .implements(unsafe { PRIORITY_QUEUE_INTERFACE_CE.unwrap() })
        .object_override::<PriorityQueue>();

    let class = builder.build().unwrap_or_else(|_| {
        panic!(
            "Unable to build class `{}`",
            PRIORITY_QUEUE_IMPLMENTATION_CLASS_NAME
        )
    });

    PRIORITY_QUEUE_IMPLMENTATION_METADATA.set_ce(class);
}

pub struct PriorityQueue {
    queue: HashMap<i64, Vec<Zval>>,
}

impl PriorityQueue {
    pub fn construct(ex: &mut ExecuteData) -> ConstructorResult<Self> {
        if ex.parser().parse().is_err() {
            return ConstructorResult::ArgError;
        }

        Self {
            queue: HashMap::new(),
        }
        .into()
    }

    pub extern "C" fn enqueue(ex: &mut ExecuteData, retval: &mut Zval) {
        let mut node = Arg::new("node", DataType::Mixed);
        let mut priority = Arg::new("priority", DataType::Long).default("0".to_string());
        let this: &mut ZendClassObject<PriorityQueue> = parse_method!(ex, node, ?= priority);

        let node: &Zval = parse_zval_argument!(node, "node");
        let priority: i64 = parse_argument!(priority, "priority", 0);

        this.queue
            .entry(priority)
            .and_modify(|v| v.push(node.shallow_clone()))
            .or_insert_with(|| vec![node.shallow_clone()]);

        retval.set_null();
    }

    pub extern "C" fn peek(ex: &mut ExecuteData, retval: &mut Zval) {
        let this: &mut ZendClassObject<PriorityQueue> = parse_method!(ex);

        // retrieve the highest priority.
        match this.queue.keys().max().copied() {
            Some(highest) => {
                // retrieve the list of nodes with the priority `highest`.
                let nodes = this.queue.get_mut(&highest).unwrap();
                let node = &nodes[0];

                *retval = node.shallow_clone();
            }
            None => {
                retval.set_null();
            }
        }
    }

    pub extern "C" fn pull(ex: &mut ExecuteData, retval: &mut Zval) {
        let this: &mut ZendClassObject<PriorityQueue> = parse_method!(ex);
        match this.pop() {
            Some(zval) => {
                *retval = zval;
            }
            None => {
                retval.set_null();
            }
        }
    }

    pub extern "C" fn dequeue(ex: &mut ExecuteData, retval: &mut Zval) {
        let this: &mut ZendClassObject<PriorityQueue> = parse_method!(ex);

        match this.pop() {
            Some(zval) => {
                *retval = zval;
            }
            None => {
                let ce = unsafe { UNDERFLOW_EXCEPTION_CE.unwrap() };

                throw!(ce, "Cannot dequeue a node from an empty queue.");
            }
        }
    }

    fn pop(&mut self) -> Option<Zval> {
        // retrieve the highest priority.
        let highest = self.queue.keys().max().copied()?;
        // retrieve the list of nodes with the priority `highest`.
        let nodes = self.queue.get_mut(&highest)?;
        // shift the first node out.
        let node = nodes.remove(0);
        // If the vec contained only this node, remove it.
        if nodes.is_empty() {
            self.queue.remove(&highest);
        }

        Some(node)
    }

    pub extern "C" fn count(ex: &mut ExecuteData, retval: &mut Zval) {
        let this: &mut ZendClassObject<PriorityQueue> = parse_method!(ex);

        retval.set_long(this.queue.iter().map(|v| v.1.len() as i64).sum::<i64>());
    }
}

impl RegisteredClass for PriorityQueue {
    const CLASS_NAME: &'static str = PRIORITY_QUEUE_IMPLMENTATION_CLASS_NAME;

    const CONSTRUCTOR: Option<ConstructorMeta<Self>> = Some(ConstructorMeta {
        constructor: Self::construct,
        build_fn: |func: FunctionBuilder| -> FunctionBuilder { func },
    });

    fn get_metadata() -> &'static ClassMetadata<Self> {
        &PRIORITY_QUEUE_IMPLMENTATION_METADATA
    }

    fn get_properties<'a>() -> HashMap<&'static str, Property<'a, Self>> {
        HashMap::new()
    }
}

implement_class!(PriorityQueue, PRIORITY_QUEUE_IMPLMENTATION_CLASS_NAME);
