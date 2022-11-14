#[no_mangle]
pub extern "C" fn to_base(mut number: u64, base: u64) -> *const std::ffi::c_char {
    let mut result = vec![];

    loop {
        let n = number % base;
        number = number / base;

        // panics if `base` is < 2 or > 32, PSL php implementation
        // also fails, and considers passing invalid value
        // an undefined behavior, so panicing is not really an
        // issue, respect the function signature.
        result.push(std::char::from_digit(n.try_into().unwrap(), base.try_into().unwrap()).unwrap());
        if number == 0 {
            break;
        }
    }

    let string = result.into_iter().rev().collect::<String>();
    let c_string = std::ffi::CString::new(string).unwrap();
    let pointer = c_string.as_ptr();
    std::mem::forget(c_string);

    pointer
}

#[no_mangle]
pub extern "C" fn from_base(number: *const std::ffi::c_char, base: u32) -> u64 {
    let c_number: &std::ffi::CStr = unsafe {
        std::ffi::CStr::from_ptr(number)
    };

    u64::from_str_radix(c_number.to_str().unwrap(), base).unwrap()
}
