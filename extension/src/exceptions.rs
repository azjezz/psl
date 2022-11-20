use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::zend::ce;
use ext_php_rs::zend::ClassEntry;

pub static mut EXCEPTION_INTERFACE_CE: Option<&'static ClassEntry> = None;
pub static mut INVARIANT_VIOLATION_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut INVALID_ARGUMENT_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut LOGIC_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut RUNTIME_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut OUT_OF_BOUNDS_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut OVERFLOW_EXCEPTION_CE: Option<&'static ClassEntry> = None;
pub static mut UNDERFLOW_EXCEPTION_CE: Option<&'static ClassEntry> = None;

pub fn build() {
    // SPL Exceptions:
    let spl_runtime_exception_ce = ClassEntry::try_find("RuntimeException").unwrap_or_else(|| {
        ClassBuilder::new("RuntimeException")
            .extends(ce::exception())
            .build()
            .unwrap()
    });

    let spl_logic_exception_ce = ClassEntry::try_find("LogicException").unwrap_or_else(|| {
        ClassBuilder::new("LogicException")
            .extends(ce::exception())
            .build()
            .unwrap()
    });

    let spl_invalid_argument_exception_ce = ClassEntry::try_find("InvalidArgumentException")
        .unwrap_or_else(|| {
            ClassBuilder::new("InvalidArgumentException")
                .extends(spl_logic_exception_ce)
                .build()
                .unwrap()
        });

    let spl_out_of_bounds_exception_ce = ClassEntry::try_find("OutOfBoundsException")
        .unwrap_or_else(|| {
            ClassBuilder::new("OutOfBoundsException")
                .extends(spl_runtime_exception_ce)
                .build()
                .unwrap()
        });

    let spl_overflow_exception_ce =
        ClassEntry::try_find("OverflowException").unwrap_or_else(|| {
            ClassBuilder::new("OverflowException")
                .extends(spl_runtime_exception_ce)
                .build()
                .unwrap()
        });

    let spl_underflow_exception_ce =
        ClassEntry::try_find("UnderflowException").unwrap_or_else(|| {
            ClassBuilder::new("UnderflowException")
                .extends(spl_runtime_exception_ce)
                .build()
                .unwrap()
        });

    // `Psl\Exception`:
    let exception_interface_ce = ClassBuilder::new("Psl\\Exception\\ExceptionInterface")
        .implements(ce::throwable())
        .flags(ClassFlags::Interface)
        .build()
        .unwrap();

    let runtime_exception_ce = ClassBuilder::new("Psl\\Exception\\RuntimeException")
        .implements(exception_interface_ce)
        .extends(spl_runtime_exception_ce)
        .build()
        .unwrap();

    let logic_exception_ce = ClassBuilder::new("Psl\\Exception\\LogicException")
        .implements(exception_interface_ce)
        .extends(spl_logic_exception_ce)
        .build()
        .unwrap();

    let invalid_argument_exception_ce =
        ClassBuilder::new("Psl\\Exception\\InvalidArgumentException")
            .implements(exception_interface_ce)
            .extends(spl_invalid_argument_exception_ce)
            .build()
            .unwrap();

    let out_of_bounds_exception_ce = ClassBuilder::new("Psl\\Exception\\OutOfBoundsException")
        .implements(exception_interface_ce)
        .extends(spl_out_of_bounds_exception_ce)
        .build()
        .unwrap();

    let overflow_exception_ce = ClassBuilder::new("Psl\\Exception\\OverflowException")
        .implements(exception_interface_ce)
        .extends(spl_overflow_exception_ce)
        .build()
        .unwrap();

    let underflow_exception_ce = ClassBuilder::new("Psl\\Exception\\UnderflowException")
        .implements(exception_interface_ce)
        .extends(spl_underflow_exception_ce)
        .build()
        .unwrap();

    let invaraint_violation_exception_ce =
        ClassBuilder::new("Psl\\Exception\\InvariantViolationException")
            .extends(runtime_exception_ce)
            .flags(ClassFlags::Final)
            .build()
            .unwrap();

    unsafe {
        EXCEPTION_INTERFACE_CE.replace(exception_interface_ce);
        INVARIANT_VIOLATION_EXCEPTION_CE.replace(invaraint_violation_exception_ce);
        RUNTIME_EXCEPTION_CE.replace(runtime_exception_ce);
        LOGIC_EXCEPTION_CE.replace(logic_exception_ce);
        INVALID_ARGUMENT_EXCEPTION_CE.replace(invalid_argument_exception_ce);
        OUT_OF_BOUNDS_EXCEPTION_CE.replace(out_of_bounds_exception_ce);
        OVERFLOW_EXCEPTION_CE.replace(overflow_exception_ce);
        UNDERFLOW_EXCEPTION_CE.replace(underflow_exception_ce);
    }
}
