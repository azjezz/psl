use ext_php_rs::convert::IntoZvalDyn;
use ext_php_rs::error::Result;
use ext_php_rs::ffi;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ClassEntry;

pub unsafe fn make_exception(
    class: Option<&'static ClassEntry>,
    arguments: Vec<&dyn IntoZvalDyn>,
) -> Result<Zval> {
    let class = class.unwrap();
    let len = arguments.len();
    let arguments = arguments
        .into_iter()
        .map(|val| val.as_zval(false))
        .collect::<Result<Vec<_>>>()?
        .into_boxed_slice();

    let class_ptr = class as *const _ as *mut _;
    let constructor_ptr = class.constructor;
    let object = class.__bindgen_anon_2.create_object.unwrap()(class_ptr);

    ffi::zend_call_known_function(
        constructor_ptr,
        object,
        class_ptr,
        std::ptr::null_mut(),
        len as _,
        arguments.as_ptr() as _,
        std::ptr::null_mut(),
    );

    let object = object
        .as_mut()
        .expect("error: failed to allocate memory for object");

    let mut result = Zval::new();
    result.set_object(object);

    Ok(result)
}
