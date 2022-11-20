use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::zend::ClassEntry;

use crate::exceptions::EXCEPTION_INTERFACE_CE as ROOT_EXCEPTION_INTERFACE_CE;
use crate::exceptions::UNDERFLOW_EXCEPTION_CE as ROOT_UNDERFLOW_EXCEPTION_CE;

pub static mut EXCEPTION_INTERFACE_CE: Option<&'static ClassEntry> = None;
pub static mut UNDERFLOW_EXCEPTION_CE: Option<&'static ClassEntry> = None;

pub fn build() {
    let root_exception_interface_ce = unsafe { ROOT_EXCEPTION_INTERFACE_CE.unwrap() };
    let root_underflow_ce = unsafe { ROOT_UNDERFLOW_EXCEPTION_CE.unwrap() };

    let interface_ce = ClassBuilder::new("Psl\\DataStructure\\Exception\\ExceptionInterface")
        .implements(root_exception_interface_ce)
        .flags(ClassFlags::Interface)
        .build()
        .unwrap();

    let underflow_ce = ClassBuilder::new("Psl\\DataStructure\\Exception\\UnderflowException")
        .extends(root_underflow_ce)
        .implements(interface_ce)
        .flags(ClassFlags::Final)
        .build()
        .unwrap();

    unsafe {
        EXCEPTION_INTERFACE_CE.replace(interface_ce);
        UNDERFLOW_EXCEPTION_CE.replace(underflow_ce);
    };
}
