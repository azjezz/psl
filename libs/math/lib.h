#include <stdarg.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdlib.h>

const char *to_base(uint64_t number, uint64_t base);

uint64_t from_base(const char *number, uint32_t base);
