use ext_php_rs::args::Arg;
use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::builders::FunctionBuilder;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::flags::DataType;
use ext_php_rs::flags::MethodFlags;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ClassEntry;
use ext_php_rs::zend::ExecuteData;

use crate::components::helpers::make_exception;
use crate::exceptions::EXCEPTION_INTERFACE_CE as ROOT_EXCEPTION_INTERFACE_CE;
use crate::exceptions::OUT_OF_BOUNDS_EXCEPTION_CE as ROOT_OUT_OF_BOUNDS_EXCEPTION_CE;
use crate::exceptions::RUNTIME_EXCEPTION_CE as ROOT_RUNTIME_EXCEPTION_CE;

pub static mut EXCEPTION_INTERFACE_CE: Option<&'static ClassEntry> = None;
pub static mut CLOSED_CHANNEL_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut EMPTY_CHANNEL_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut FULL_CHANNEL_EXCEPTION_CE: Option<&'static ClassEntry> = None;

pub fn build() {
    let root_exception_interface_ce = unsafe { ROOT_EXCEPTION_INTERFACE_CE.unwrap() };
    let root_runtime_exception_ce = unsafe { ROOT_RUNTIME_EXCEPTION_CE.unwrap() };
    let root_out_of_bounds_exception_ce = unsafe { ROOT_OUT_OF_BOUNDS_EXCEPTION_CE.unwrap() };

    let interface_ce = ClassBuilder::new("Psl\\Channel\\Exception\\ExceptionInterface")
        .implements(root_exception_interface_ce)
        .flags(ClassFlags::Interface)
        .build()
        .unwrap();

    let closed_channel_ce = ClassBuilder::new("Psl\\Channel\\Exception\\ClosedChannelException")
        .implements(interface_ce)
        .extends(root_runtime_exception_ce)
        .flags(ClassFlags::Final)
        .method(
            FunctionBuilder::new("forSending", closed_for_sending)
                .returns(
                    DataType::Object(Some("Psl\\Channel\\Exception\\ClosedChannelException")),
                    false,
                    false,
                )
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::Static,
        )
        .method(
            FunctionBuilder::new("forReceiving", closed_for_reciving)
                .returns(
                    DataType::Object(Some("Psl\\Channel\\Exception\\ClosedChannelException")),
                    false,
                    false,
                )
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::Static,
        )
        .build()
        .unwrap();

    let empty_channel_ce = ClassBuilder::new("Psl\\Channel\\Exception\\EmptyChannelException")
        .implements(interface_ce)
        .extends(root_out_of_bounds_exception_ce)
        .flags(ClassFlags::Final)
        .method(
            FunctionBuilder::new("create", empty_create)
                .returns(
                    DataType::Object(Some("Psl\\Channel\\Exception\\EmptyChannelException")),
                    false,
                    false,
                )
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::Static,
        )
        .build()
        .unwrap();

    let full_channel_ce = ClassBuilder::new("Psl\\Channel\\Exception\\FullChannelException")
        .implements(interface_ce)
        .extends(root_out_of_bounds_exception_ce)
        .flags(ClassFlags::Final)
        .method(
            FunctionBuilder::new("ofCapacity", full_create)
                .arg(Arg::new("capacity", DataType::Long))
                .returns(
                    DataType::Object(Some("Psl\\Channel\\Exception\\FullChannelException")),
                    false,
                    false,
                )
                .build()
                .unwrap(),
            MethodFlags::Public | MethodFlags::Static,
        )
        .build()
        .unwrap();

    unsafe {
        EXCEPTION_INTERFACE_CE.replace(interface_ce);
        CLOSED_CHANNEL_EXCEPTION_CE.replace(closed_channel_ce);
        EMPTY_CHANNEL_EXCEPTION_CE.replace(empty_channel_ce);
        FULL_CHANNEL_EXCEPTION_CE.replace(full_channel_ce);
    };
}

pub extern "C" fn closed_for_sending(ex: &mut ExecuteData, retval: &mut Zval) {
    if ex.parser().parse().is_err() {
        return;
    }

    let message = "Attempted to send a message to a closed channel.";
    let exception = unsafe { make_exception(CLOSED_CHANNEL_EXCEPTION_CE, vec![&message]) };

    *retval = exception.unwrap();
}

pub extern "C" fn closed_for_reciving(ex: &mut ExecuteData, retval: &mut Zval) {
    if ex.parser().parse().is_err() {
        return;
    }

    let message = "Attempted to receive a message from a closed empty channel.";
    let exception = unsafe { make_exception(CLOSED_CHANNEL_EXCEPTION_CE, vec![&message]) };

    *retval = exception.unwrap();
}

pub extern "C" fn empty_create(ex: &mut ExecuteData, retval: &mut Zval) {
    if ex.parser().parse().is_err() {
        return;
    }

    let message = "Attempted to receive a message from an empty channel.";
    let exception = unsafe { make_exception(EMPTY_CHANNEL_EXCEPTION_CE, vec![&message]) };

    *retval = exception.unwrap();
}

pub extern "C" fn full_create(ex: &mut ExecuteData, retval: &mut Zval) {
    let mut capacity = Arg::new("capacity", DataType::Long);
    if ex.parser().arg(&mut capacity).parse().is_err() {
        return;
    }

    let capacity: i64 = parse_argument!(capacity, "capacity");
    let message = format!("Channel has reached its full capacity of {}.", capacity);
    let exception = unsafe { make_exception(FULL_CHANNEL_EXCEPTION_CE, vec![&message]) };

    *retval = exception.unwrap();
}
